<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateSuperAdmin extends Command
{
    protected $signature = 'homeease:super-admin {email?} {--name=} {--password=}';
    protected $description = 'Create or promote a HomeEase super administrator without logging the password.';

    public function handle(): int
    {
        $email = $this->argument('email') ?: $this->ask('Admin email');
        $name = $this->option('name') ?: $this->ask('Admin name', 'HomeEase Admin');
        $password = $this->option('password') ?: env('HOMEEASE_ADMIN_PASSWORD') ?: $this->secret('Admin password');
        if (!$email || !$password || strlen($password) < 8) { $this->error('A valid email and password of at least 8 characters are required.'); return self::FAILURE; }
        $user = User::updateOrCreate(['email' => strtolower($email)], ['name' => $name, 'password' => Hash::make($password), 'role' => 'super_admin', 'is_active' => true, 'email_verified_at' => now()]);
        $this->info("Super admin ready for {$user->email}. Password was not printed or logged.");
        return self::SUCCESS;
    }
}
