<?php

namespace Zxin\Think\Auth;

class AuthContext
{
    /**
     * @var string
     */
    protected $url;
    /**
     * @var array|null
     */
    protected $feature;

    /**
     * @var string[]|null
     */
    protected $permissions;

    /**
     * @var bool
     */
    protected $allow;

    /**
     * @var array|null
     */
    protected $permissionsDetails;

    /**
     * AuthContext constructor.
     */
    protected function __construct()
    {
    }

    /**
     * @return bool
     */
    public static function has(): bool
    {
        return app()->has(AuthContext::class);
    }

    public static function get(): ?AuthContext
    {
        $app = app();
        return $app->has(AuthContext::class) ? $app->get(AuthContext::class) : null;
    }

    /**
     * @param string   $url
     * @param string[] $permissions
     * @param bool     $allow
     * @return AuthContext
     */
    public static function create(string $url, ?array $permissions, bool $allow): AuthContext
    {
        $ctx              = new AuthContext();
        $ctx->url         = $url;
        $ctx->feature     = Permission::getInstance()->queryFeature($url);
        $ctx->permissions = $permissions;
        $ctx->allow       = $allow;

        app()->bind(AuthContext::class, $ctx);
        return $ctx;
    }

    /**
     * @param string $url
     * @return AuthContext
     */
    public static function createSuperRoot(string $url): AuthContext
    {
        return self::create($url, null, true);
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return array|null
     */
    public function getFeature(): ?array
    {
        return $this->feature;
    }

    /**
     * @return array|null
     */
    public function getPermissions(): ?array
    {
        return $this->permissions;
    }

    /**
     * @return string|null
     */
    public function getPermissionsLine(): ?string
    {
        return $this->permissions === null ? null : implode(',', $this->permissions);
    }

    /**
     * @return array<string, array|null>
     */
    public function getPermissionsDetails(): ?array
    {
        if ($this->permissions === null) {
            return null;
        }
        if ($this->permissionsDetails !== null) {
            return $this->permissionsDetails;
        }
        $details          = [];
        $permissionObject = Permission::getInstance();
        foreach ($this->permissions as $permission) {
            $details[$permission] = $permissionObject->queryPermission($permission);
        }
        return $this->permissionsDetails = $details;
    }

    /**
     * @return bool
     */
    public function isAllow(): bool
    {
        return $this->allow;
    }
}
