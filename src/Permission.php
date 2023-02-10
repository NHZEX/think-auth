<?php

declare(strict_types=1);

namespace Zxin\Think\Auth;

use think\App;
use Zxin\Think\Auth\Exception\AuthException;

class Permission
{
    protected ?AuthStorage $storage = null;

    public static function getInstance($reset = false): Permission
    {
        $app = App::getInstance();
        if ($reset) {
            $app->delete(Permission::class);
        }
        return $app->make(Permission::class);
    }

    public static function getDumpFilePath(string $filename = 'auth_storage.php'): string
    {
        $path = App::getInstance()->config->get('auth.dump_file_path');
        if (empty($path)) {
            $path = App::getInstance()->getAppPath();
        }
        $path = str_replace('\\', '/', $path);
        if (!str_ends_with($path, '/')) {
            $path .= '/';
        }
        if (!is_dir($path)) {
            throw new AuthException("{$path} does not exist");
        }
        return $path . $filename;
    }

    public function __construct()
    {
        $this->loadStorage();
    }

    /**
     * 获取树
     * @param string        $index
     * @param int           $level
     * @param array|null    $data
     * @param callable|null $filter
     * @return array
     */
    public function getTree(
        string $index = '__ROOT__',
        int $level = 0,
        ?array $data = null,
        callable $filter = null
    ): array {
        if (null === $data) {
            $data = array_merge([], $this->loadStorage()->permission);
            if (\is_callable($data)) {
                $data = $filter($data);
            }
            usort($data, fn ($a, $b) => $a['sort'] <=> $b['sort']);
        }
        $tree = [];
        foreach ($data as $permission) {
            if ($permission['pid'] === $index) {
                $permission['title'] = $permission['name'];
                $permission['spread'] = true;
                $permission['valid'] = !empty($permission['allow']);
                $permission['children'] = $this->getTree($permission['title'], $level + 1, $data);
                $tree[] = $permission;
            }
        }
        return $tree;
    }

    protected function loadStorage(): ?AuthStorage
    {
        if (empty($this->storage)) {
            $filename = Permission::getDumpFilePath();
            $this->storage = new AuthStorage(require $filename);
        }
        return $this->storage;
    }

    /**
     * @return bool
     */
    public function hasStorage(): bool
    {
        return $this->loadStorage() !== null;
    }

    /**
     * @return AuthStorage
     */
    public function getStorage(): AuthStorage
    {
        return $this->storage;
    }

    /**
     * @return array
     */
    public function getPermission(): array
    {
        return $this->loadStorage()->permission;
    }

    /**
     * @param array $permission
     * @return void
     */
    public function setPermission(array $permission): void
    {
        $this->loadStorage()->permission = $permission;
    }

    /**
     * 查询节点
     * @param string $node
     * @return array|null
     */
    public function queryFeature(string $node): ?array
    {
        return $this->loadStorage()->features[$node] ?? null;
    }

    /**
     * 查询权限
     * @param string $name
     * @return array|null
     */
    public function queryPermission(string $name): ?array
    {
        return $this->loadStorage()->permission[$name] ?? null;
    }

    public function getPermissionsByFeature($feature): ?array
    {
        return $this->loadStorage()->fe2pe[$feature] ?? null;
    }

    public function allPermission()
    {
        return $this->loadStorage()->pe2fe;
    }

    public function contain(string $node)
    {
        $storage = $this->loadStorage();
        return isset($storage->pe2fe[$node]) || isset($storage->fe2pe[$node]);
    }
}
