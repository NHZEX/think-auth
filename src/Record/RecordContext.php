<?php

namespace Zxin\Think\Auth\Record;

use Throwable;
use function get_class;
use function sprintf;

class RecordContext
{
    /** @var string */
    protected $message = '';

    /** @var int */
    protected $code = 0;

    /** @var array|null */
    protected $extra = null;

    /**
     * @param string $message
     * @return RecordContext
     */
    public function setMessage(string $message): RecordContext
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @param int $code
     * @return RecordContext
     */
    public function setCode(int $code): RecordContext
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @param Throwable $throwable
     * @return RecordContext
     */
    public function setException(Throwable $throwable): RecordContext
    {
        $this->code = $throwable->getCode();
        $this->message = sprintf('%s [%s]', $throwable->getMessage(), get_class($throwable));
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return array|null
     */
    public function getExtra(): ?array
    {
        return $this->extra;
    }

    /**
     * @param array|null $extra
     */
    public function setExtra(?array $extra): void
    {
        $this->extra = $extra;
    }
}
