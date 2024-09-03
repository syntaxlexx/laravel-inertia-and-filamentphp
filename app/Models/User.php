<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;
    use HasRoles;

    /*
    |--------------------------------------------------------------------------
    | System Roles
    |--------------------------------------------------------------------------
    |
    | provide a harmonized way of accessing system roles
    |
    */
    const ROLE_SUPERADMIN = 'SUPERADMIN';
    const ROLE_ADMIN = 'ADMIN';
    const ROLE_USER = 'USER';
    const ROLE_HUMANS = [
        self::ROLE_ADMIN,
        self::ROLE_USER,
    ];
    const ROLE_DEFAULT = self::ROLE_USER;
    const ROLES = [
        self::ROLE_SUPERADMIN,
        self::ROLE_ADMIN,
        self::ROLE_USER,
    ];


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->can('view-admin', User::class);
    }

    /**
     * Check if user has role
     */
    public function hasRole($roles)
    {
        if (!is_array($roles))
            $roles = [$roles];

        return in_array($this->role, $roles);
    }

    /**
     * Interact with the user's role.
     *
     * @param  string  $value
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function role(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
            set: fn ($value) => strtoupper($value),
        );
    }

    /**
     * Interact with the user's name (username).
     *
     * @param  string  $value
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtolower($value),
            set: fn ($value) => strtolower($value),
        );
    }

    /**
     * Interact with the user's email.
     *
     * @param  string  $value
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtolower($value),
            set: fn ($value) => strtolower($value),
        );
    }

    /**
     * An alias to get the username (name)
     */
    public function username(): Attribute
    {
        return Attribute::make(
            get: fn ($val, $attr) => $this->name,
        );
    }

    public function isSudo(): Attribute
    {
        return Attribute::make(
            get: fn ($val, $attr) => in_array($this->role, [User::ROLE_SUPERADMIN]),
        )->shouldCache();
    }

    public function isAdminOrSudo(): Attribute
    {
        return Attribute::make(
            get: fn ($val, $attr) => in_array($this->role, [User::ROLE_SUPERADMIN, User::ROLE_ADMIN]),
        )->shouldCache();
    }

    public function isAdmin(): Attribute
    {
        $shouldAddSudo = true;

        return Attribute::make(
            get: fn ($val, $attr) => $shouldAddSudo ? in_array($this->role, [User::ROLE_SUPERADMIN, User::ROLE_ADMIN]) : $this->role === User::ROLE_ADMIN,
        )->shouldCache();
    }
}
