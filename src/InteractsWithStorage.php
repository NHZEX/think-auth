<?php
declare(strict_types=1);

namespace Zxin\Think\Auth;

use function array_pop;
use function array_values;
use function count;
use function explode;
use function implode;
use function ksort;

/**
 * Trait InteractsWithStorage
 * @package Zxin\Think\Auth
 */
trait InteractsWithStorage
{
    protected function build(): array
    {
        $output = [
            'features'   => $this->nodes,
            'permission' => $this->fillPermission($this->permissions, []),
            'permission2features' => [],
            'features2permission' => [],
        ];

        $permission = Permission::getInstance();
        if ($permission->hasStorage()) {
            foreach ($output['permission'] as $key => $item) {
                if ($info = $permission->queryPermission($key)) {
                    $item['sort'] = (int) $info['sort'];
                    $item['desc'] = $info['desc'];
                    $output['permission'][$key] = $item;
                }
            }
        }

        $permission2features = &$output['permission2features'];
        foreach ($output['permission'] as $permission => $data) {
            $permission2features[$permission] = array_merge(
                $permission2features[$permission] ?? [],
                $data['allow'] ?? []
            );
        }

        $features2permission = &$output['features2permission'];
        foreach ($output['permission2features'] as $permission => $features) {
            foreach ($features as $feature) {
                $features2permission[$feature][$permission] = true;
            }
        }

        return $output;
    }

    /**
     * @param array $data
     * @param array $original
     * @return array
     */
    protected function fillPermission(array $data, array $original): array
    {
        $result = [];
        $original = $original['permission'] ?? [];
        foreach ($data as $permission => $control) {
            // 填充父节点
            $pid = $this->fillParent($result, $original, $permission);
            // 生成插入数据
            if (isset($original[$permission])) {
                $sort = $original[$permission]['sort'];
                $desc = $original[$permission]['desc'];
            } else {
                $sort = 0;
                $desc = '';
            }
            if (isset($control['desc']) || isset($control['allow'])) {
                $result[$permission] = [
                    'pid' => $pid,
                    'name' => $permission,
                    'sort' => $control['sort'] ?? $sort,
                    'desc' => $control['desc'] ?? $desc,
                    'allow' => $control['allow'] ?? null,
                ];
            } else {
                $result[$permission] = [
                    'pid' => $pid,
                    'name' => $permission,
                    'sort' => $sort,
                    'desc' => $desc,
                    'allow' => array_values($control),
                ];
            }
        }

        ksort($result);
        return $result;
    }


    /**
     * 填充父节点
     * @param array  $data
     * @param array  $original
     * @param string $permission
     * @return string
     */
    protected function fillParent(array &$data, array $original, string $permission): string
    {
        $delimiter = '.';
        $parents = explode($delimiter, $permission) ?: [];
        if (1 === count($parents)) {
            return self::ROOT_NODE;
        }
        array_pop($parents);
        $result = implode($delimiter, $parents);

        while (count($parents)) {
            $curr = implode($delimiter, $parents);
            array_pop($parents);
            $parent = implode($delimiter, $parents) ?: self::ROOT_NODE;

            if (isset($original[$curr])) {
                $sort = $original[$curr]['sort'];
                $desc = $original[$curr]['desc'];
            } else {
                $sort = 0;
                $desc = '';
            }
            $data[$curr] = [
                'pid' => $parent,
                'name' => $curr,
                'sort' => $sort,
                'desc' => $desc,
                'allow' => $data[$curr]['allow'] ?? null,
            ];
        }

        return $result;
    }
}
