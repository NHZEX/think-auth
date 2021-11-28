<?php

namespace Zxin\Think\Auth\Record;

use think\Request;
use think\Response;
use Zxin\Think\Auth\AuthContext;

interface RecordAdapterInterface
{
    public function isActivity(Request $request, Response $response): bool;

    public function writeRecord(Request $request, Response $response, ?RecordContext $recordContext, ?AuthContext $authContext): void;
}
