<?php

use App\Models\Pod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Pod::whereNotNull('admin_username')->orWhereNotNull('admin_password')->chunk(100, function ($pods) {
            foreach ($pods as $pod) {
                $needsSave = false;

                if ($pod->admin_username && !$this->isEncrypted($pod->admin_username)) {
                    $pod->admin_username = encrypt($pod->admin_username);
                    $needsSave = true;
                }

                if ($pod->admin_password && !$this->isEncrypted($pod->admin_password)) {
                    $pod->admin_password = encrypt($pod->admin_password);
                    $needsSave = true;
                }

                if ($needsSave) {
                    $pod->saveQuietly();
                }
            }
        });
    }

    public function down(): void
    {
        Pod::whereNotNull('admin_username')->orWhereNotNull('admin_password')->chunk(100, function ($pods) {
            foreach ($pods as $pod) {
                $needsSave = false;

                if ($pod->admin_username && $this->isEncrypted($pod->admin_username)) {
                    $pod->admin_username = decrypt($pod->admin_username);
                    $needsSave = true;
                }

                if ($pod->admin_password && $this->isEncrypted($pod->admin_password)) {
                    $pod->admin_password = decrypt($pod->admin_password);
                    $needsSave = true;
                }

                if ($needsSave) {
                    $pod->saveQuietly();
                }
            }
        });
    }

    private function isEncrypted(string $value): bool
    {
        return str_starts_with($value, 'eyJpdiI') || str_starts_with($value, '{"iv":');
    }
};
