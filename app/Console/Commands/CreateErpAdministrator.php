<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateErpAdministrator extends Command
{
    protected $signature = 'erp:create-admin {email : Administrator work email} {--name= : Administrator name}';

    protected $description = 'Create or update an ERP administrator and grant access to every company';

    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $name = (string) ($this->option('name') ?: $this->ask('Administrator name'));
        $password = (string) $this->secret('Password (minimum 12 characters)');

        $validator = Validator::make(compact('email', 'name', 'password'), [
            'email' => ['required', 'email'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:12'],
        ]);
        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make($password), 'email_verified_at' => now()]
        );
        Company::query()->each(fn (Company $company) => $user->companies()->syncWithoutDetaching([
            $company->id => ['role' => 'admin', 'is_active' => true],
        ]));

        $this->info("Administrator {$email} is ready.");

        return self::SUCCESS;
    }
}
