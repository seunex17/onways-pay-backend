<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseFuelVoucherRequest;
use App\Http\Resources\FuelVoucherResource;
use App\Models\FuelVoucher;
use App\Models\FuelVoucherPurchase;
use App\Models\Transaction;
use App\Services\FuelVoucherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class FuelVoucherController extends Controller
{
    public function __construct(private FuelVoucherService $service) {}

    public function purchase(PurchaseFuelVoucherRequest $request): JsonResponse
    {
        $idempotencyKey = $request->header('Idempotency-Key');

        if (! is_string($idempotencyKey) || mb_strlen($idempotencyKey) > 255 || mb_strlen($idempotencyKey) < 8) {
            return response()->json(['message' => __('fuel_voucher_idempotency_key_required')], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $purchase = $this->service->purchase($request->user(), $request->validated(), $idempotencyKey);

        return response()->json($this->response($purchase), $purchase->status === 'completed'
            ? Response::HTTP_CREATED
            : Response::HTTP_ACCEPTED);
    }

    public function status(Request $request, Transaction $transaction): JsonResponse
    {
        abort_unless((int) $transaction->user_id === (int) $request->user()->id, Response::HTTP_NOT_FOUND);
        $purchase = $transaction->fuelVoucherPurchase()->with(['transaction', 'voucher'])->firstOrFail();

        return response()->json($this->response($purchase));
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:pending,active,redeemed,expired,cancelled'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $vouchers = FuelVoucher::query()->where('user_id', $request->user()->id)
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()->paginate($validated['per_page'] ?? 20);

        return FuelVoucherResource::collection($vouchers);
    }

    public function show(Request $request, FuelVoucher $voucher): FuelVoucherResource
    {
        $this->authorizeVoucher($request, $voucher);

        return new FuelVoucherResource($voucher);
    }

    public function download(Request $request, FuelVoucher $voucher): Response
    {
        $this->authorizeVoucher($request, $voucher);
        $code = e($voucher->code);
        $reference = e($voucher->reference);
        $title = e(__('fuel_voucher_title'));
        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" width="900" height="500"><rect width="100%" height="100%" fill="#fff"/><text x="50" y="100" font-size="36">{$title}</text><text x="50" y="180" font-size="24">{$reference}</text><text x="50" y="250" font-size="42">{$code}</text><text x="50" y="330" font-size="24">{$voucher->litres} L · {$voucher->amount} {$voucher->currency}</text></svg>
        SVG;

        return response($svg)->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'attachment; filename="'.$voucher->reference.'.svg"')
            ->header('Cache-Control', 'private, no-store');
    }

    /** @return array<string, mixed> */
    private function response(FuelVoucherPurchase $purchase): array
    {
        $purchase->loadMissing(['transaction', 'voucher']);
        $completed = $purchase->status === 'completed';

        return [
            'status' => $purchase->status,
            'message' => $completed ? __('fuel_voucher_issued') : __('fuel_voucher_authorization_required'),
            'transaction' => $purchase->transaction,
            'payment' => $completed ? null : [
                'provider' => $purchase->provider,
                'status' => $purchase->status,
                'partner_reference' => $purchase->touchpay_reference,
                'expires_at' => null,
            ],
            'voucher' => $purchase->voucher ? (new FuelVoucherResource($purchase->voucher))->resolve() : null,
        ];
    }

    private function authorizeVoucher(Request $request, FuelVoucher $voucher): void
    {
        $isPurchaser = (int) $voucher->user_id === (int) $request->user()->id;
        $isRecipient = $request->user()->phone === $voucher->recipient_phone;
        abort_unless($isPurchaser || $isRecipient, Response::HTTP_NOT_FOUND);
    }
}
