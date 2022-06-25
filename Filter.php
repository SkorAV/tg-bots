<?php

class Filter
{
    protected string $field;
    protected string|int|bool|null $value;
    private string $prefix;
    private string $tableOrAlias;

    public function __construct(string $field, string|int|bool|null $value, string $tableOrAlias = '')
    {
        $this->field = $field;
        $this->value = $value;
        $this->prefix = md5(time());
        $this->tableOrAlias = $tableOrAlias;
    }

    public function __toString(): string
    {
        return "{$this->tableOrAlias}.{$this->field} = :{$this->prefix}_{$this->field}";
    }

    public function getBind(): array
    {
        return [$this->prefix . '_' . $this->field => $this->value];
    }
}