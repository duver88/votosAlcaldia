<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'user_cedula',
        'action',
        'description',
        'ip_address',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public static function log(string $action, ?string $description = null, ?string $userCedula = null, ?int $adminId = null): self
    {
        return self::create([
            'action' => $action,
            'description' => $description,
            'user_cedula' => $userCedula,
            'admin_id' => $adminId,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }
}
