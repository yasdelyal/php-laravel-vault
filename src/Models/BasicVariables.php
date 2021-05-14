<?php

namespace YaSdelyal\LaravelVault\Models;

use JsonException;
use YaSdelyal\LaravelVault\Contracts\Variables;

class BasicVariables implements Variables
{
    private $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function toArray()
    {
        return $this->data;
    }

    public function toJson($options = 0)
    {
        $json = json_encode($this->jsonSerialize(), $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new JsonException(json_last_error_msg());
        }

        return $json;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function keys(): array
    {
        return array_keys($this->data);
    }

    public function get($key): ?string
    {
        return (string) $this->data[$key] ?? null;
    }

    public function has($key): bool
    {
        return isset($this->data[$key]);
    }

    public function merge(Variables $variables): Variables
    {
        $this->data = array_merge($this->data, $variables->toArray());
        return $this;
    }

    public function isEmpty(): bool
    {
        return count($this->data) === 0;
    }

    public function toEnv(): string
    {
        $content = '';
        foreach ($this->data as $key => $value) {
            //TODO maybe formater? or move to service?
            $content .= $key . '=' . $value . "\n";
        }
        return $content;
    }
}
