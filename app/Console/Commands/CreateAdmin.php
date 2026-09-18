<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

use function Laravel\Prompts\password;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create {email}';

    protected $description = 'Create or update the administrator user';

    public function handle(): int
    {
        $email = filter_var($this->argument('email'), FILTER_VALIDATE_EMAIL);

        if ($email === false) {
            $this->error('A valid administrator email is required.');

            return self::FAILURE;
        }

        $password = config('admin.bootstrap_password');

        if (! $password && stream_isatty(STDIN)) {
            $password = password('Administrator password', required: true);
        }

        if (! is_string($password) || $password === '') {
            $this->error('Set ADMIN_BOOTSTRAP_PASSWORD for non-interactive use.');

            return self::FAILURE;
        }

        User::updateOrCreate(
            ['email' => $email],
            ['name' => config('admin.name'), 'password' => Hash::make($password)],
        );

        $this->info('Administrator is ready.');

        return self::SUCCESS;
    }
}
