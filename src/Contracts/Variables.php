<?php

namespace YaSdelyal\LaravelVault\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

interface Variables extends Arrayable, Jsonable, JsonSerializable
{
    public function keys(): array;
    public function get($key): ?string;
    public function has($key): bool;
    public function merge(Variables $variables): self;
    public function isEmpty(): bool;
    public function toEnv(): string;
}
