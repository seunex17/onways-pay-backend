<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/pulse (PULSE) - v1
- laravel/reverb (REVERB) - v1
- laravel/sanctum (SANCTUM) - v4
- laravel/telescope (TELESCOPE) - v5
- livewire/livewire (LIVEWIRE) - v4
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- laravel-echo (ECHO) - v2
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

=== climactic/laravel-credits rules ===

## Laravel Credits

A ledger-based credit system for Laravel applications. Use for virtual currencies, reward points, or any credit-based features requiring transaction tracking with full audit trails.

### Setup

Add the `HasCredits` trait to any Eloquent model:

<code-snippet name="Add HasCredits trait to a model" lang="php">
use Climactic\Credits\Traits\HasCredits;

class User extends Model
{
    use HasCredits;
}
</code-snippet>

### Core Operations

- **Add credits**: `creditAdd(float $amount, ?string $description = null, array $metadata = []): Credit`
- **Deduct credits**: `creditDeduct(float $amount, ?string $description = null, array $metadata = []): Credit`
- **Check balance**: `creditBalance(): float`
- **Check sufficient**: `hasCredits(float $amount): bool`

<code-snippet name="Add and deduct credits with metadata" lang="php">
// Add credits with metadata
$transaction = $user->creditAdd(100.00, 'Welcome bonus', [
    'campaign' => 'onboarding',
    'tier' => 'premium',
    'tags' => ['bonus', 'welcome']
]);

// Check before deducting
if ($user->hasCredits(50.00)) {
    $user->creditDeduct(50.00, 'Feature unlock', ['feature' => 'premium_export']);
}

// Get current balance
$balance = $user->creditBalance();
</code-snippet>

### Transfer Credits

Transfer credits between models atomically with database locking:

<code-snippet name="Transfer credits between users" lang="php">
$result = $sender->creditTransfer($recipient, 100.00, 'Payment', [
    'order_id' => 123,
    'type' => 'purchase'
]);
// Returns: ['sender_balance' => 900.00, 'recipient_balance' => 100.00]
</code-snippet>

### Transaction History

<code-snippet name="Get transaction history" lang="php">
// Last 10 transactions (descending by default)
$history = $user->creditHistory();

// Last 20 transactions in ascending order
$history = $user->creditHistory(20, 'asc');

// Access the credits relationship directly
$allCredits = $user->credits()->get();
</code-snippet>

### Historical Balance

Query balance at a specific point in time:

<code-snippet name="Get historical balance" lang="php">
// Balance 7 days ago
$pastBalance = $user->creditBalanceAt(now()->subDays(7));

// Balance at specific datetime
$balance = $user->creditBalanceAt(Carbon::parse('2024-01-15 10:00:00'));

// Balance at Unix timestamp
$balance = $user->creditBalanceAt(1705312800);
</code-snippet>

### Metadata Querying

Query transactions by metadata using powerful scopes:

<code-snippet name="Basic metadata queries" lang="php">
// Filter by exact metadata value
$purchases = $user->credits()
    ->whereMetadata('source', 'purchase')
    ->get();

// With comparison operators (=, !=, >, <, >=, <=, like)
$highValue = $user->credits()
    ->whereMetadata('amount', '>', 100)
    ->get();

// Combine with OR conditions
$filtered = $user->credits()
    ->whereMetadata('source', 'purchase')
    ->orWhereMetadata('source', 'refund')
    ->get();
</code-snippet>

<code-snippet name="Advanced metadata scopes" lang="php">
// Check if array contains value
$premium = $user->credits()
    ->whereMetadataContains('tags', 'premium')
    ->get();

// Check if key exists
$withOrderId = $user->credits()
    ->whereMetadataHas('order_id')
    ->get();

// Check if key is null or missing
$noOrder = $user->credits()
    ->whereMetadataNull('order_id')
    ->get();

// Check array length
$multipleTags = $user->credits()
    ->whereMetadataLength('tags', '>=', 2)
    ->get();
</code-snippet>

<code-snippet name="Chained metadata queries" lang="php">
// Complex query with multiple conditions
$filtered = $user->credits()
    ->whereMetadata('source', 'purchase')
    ->whereMetadata('category', 'electronics')
    ->whereMetadataContains('tags', 'featured')
    ->whereMetadataHas('promo_code')
    ->where('amount', '>', 50)
    ->orderBy('created_at', 'desc')
    ->get();
</code-snippet>

<code-snippet name="Convenience metadata methods" lang="php">
// Simple metadata query with limit
$purchases = $user->creditsByMetadata('source', '=', 'purchase', limit: 20);

// Multiple filter conditions
$filtered = $user->creditHistoryWithMetadata([
    ['key' => 'source', 'value' => 'purchase'],
    ['key' => 'amount', 'operator' => '>', 'value' => 100],
    ['key' => 'tags', 'value' => 'premium', 'method' => 'contains'],
    ['key' => 'order_id', 'method' => 'has'],
], limit: 25, order: 'desc');
</code-snippet>

### Nested Metadata Keys

Use dot notation for nested JSON keys:

<code-snippet name="Query nested metadata" lang="php">
// Metadata: {'user': {'tier': 'gold', 'id': 123}}
$goldUsers = $user->credits()
    ->whereMetadata('user.tier', 'gold')
    ->get();
</code-snippet>

### Events

All events dispatch after database commit for reliability:

- `CreditsAdded` - Properties: `creditable`, `credit`, `amount`, `newBalance`, `description`, `metadata`
- `CreditsDeducted` - Properties: `creditable`, `credit`, `amount`, `newBalance`, `description`, `metadata`
- `CreditsTransferred` - Properties: `sender`, `recipient`, `amount`, `senderCredit`, `recipientCredit`, `senderNewBalance`, `recipientNewBalance`, `description`, `metadata`

<code-snippet name="Listen to credit events" lang="php">
use Climactic\Credits\Events\CreditsAdded;
use Climactic\Credits\Events\CreditsDeducted;
use Climactic\Credits\Events\CreditsTransferred;

Event::listen(CreditsAdded::class, function (CreditsAdded $event) {
    Log::info("Added {$event->amount} credits", [
        'user_id' => $event->creditable->id,
        'new_balance' => $event->newBalance,
        'metadata' => $event->metadata,
    ]);
});

Event::listen(CreditsTransferred::class, function (CreditsTransferred $event) {
    Notification::send($event->recipient, new CreditsReceivedNotification($event->amount));
});
</code-snippet>

### Exception Handling

<code-snippet name="Handle insufficient credits" lang="php">
use Climactic\Credits\Exceptions\InsufficientCreditsException;

try {
    $user->creditDeduct(1000.00, 'Large purchase');
} catch (InsufficientCreditsException $e) {
    // Handle insufficient balance
    return back()->with('error', 'Insufficient credits for this purchase.');
}
</code-snippet>

### Configuration

Publish config: `php artisan vendor:publish --tag="credits-config"`

- `allow_negative_balance` (default: false) - Allow balances to go negative
- `table_name` (default: 'credits') - Database table name

### Best Practices

1. Always use metadata for transaction context (order IDs, sources, categories)
2. Use `hasCredits()` before `creditDeduct()` for better UX
3. Leverage events for async operations (notifications, analytics)
4. Use `creditBalanceAt()` for historical reporting
5. Index frequently-queried metadata keys for performance (see Database Indexing section below)

### Database Indexing for Metadata

For high-volume metadata queries, add database indexes:

**MySQL/MariaDB** - Use virtual generated columns:

<code-snippet name="MySQL metadata indexing migration" lang="php">
Schema::table('credits', function (Blueprint $table) {
    // Add virtual column extracting JSON value
    $table->string('metadata_source')
        ->virtualAs("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.source'))");

    // Index the virtual column
    $table->index('metadata_source');
});
</code-snippet>

**PostgreSQL** - Use GIN indexes on JSONB:

<code-snippet name="PostgreSQL metadata indexing migration" lang="php">
Schema::table('credits', function (Blueprint $table) {
    // GIN index for general JSONB queries
    DB::statement('CREATE INDEX credits_metadata_gin ON credits USING GIN (metadata)');

    // Or expression index for specific key
    DB::statement("CREATE INDEX credits_metadata_source ON credits ((metadata->>'source'))");
});
</code-snippet>

**SQLite** - Limited JSON indexing support. Consider MySQL/PostgreSQL for high-volume metadata queries.

### Database Support

- **MySQL/MariaDB (InnoDB)**: Full support with row-level locking
- **PostgreSQL**: Full support with advisory locks
- **SQLite**: Basic support (limited concurrency, not recommended for production)

</laravel-boost-guidelines>
