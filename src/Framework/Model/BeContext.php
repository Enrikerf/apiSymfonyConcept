<?php

namespace App\Framework\Model;

class BeContext {

    private ?array $groups = [];

    public function __construct() {
        $this->groups = [];
    }

    public function getAttribute(string $attributeName): array {
        if ($attributeName === 'groups') {
            return $this->groups;
        }

        return [];
    }

    public function hasAttribute(string $attributeName): bool {
        if ($attributeName === 'groups') {
            return true;
        }

        return false;
    }

    public function setGroups(array $groups): BeContext {
        $this->groups = $groups;

        return $this;
    }

    public function addGroups() { }
}