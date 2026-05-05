<?php
	/**
	 * Copyright (C) ZubDev Digital Media - All Rights Reserved
	 *
	 * File: PasswordUpdateMail.php
	 * Author: Zubayr Ganiyu
	 *   Email: <seunexseun@gmail.com>
	 *   Website: https://zubdev.net
	 * Date: 5/5/26
	 * Time: 2:15 PM
	 */


	namespace App\Mail;

	use App\Models\User;
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Mail\Mailable;
	use Illuminate\Mail\Mailables\Content;
	use Illuminate\Mail\Mailables\Envelope;
	use Illuminate\Queue\SerializesModels;

	class PasswordUpdateMail extends Mailable implements ShouldQueue{
		use Queueable, SerializesModels;

        public int $tries = 5;

        public array $backoff = [60, 300, 1800, 3600, 7200];

		public function __construct(
            public User $user,
        )
		{
		}


		public function envelope()
		: Envelope
		{
			return new Envelope(
				subject: 'Password Update Alert!',
			);
		}


		public function content()
		: Content
		{
			return new Content(
				view: 'emails.password-update',
                with: [
                    'user' => $this->user,
                ]
			);
		}


		public function attachments()
		: array
		{
			return [];
		}
	}
