<?php

namespace App\Enums;

enum PurchaseOrderStatus: string
{
    case DRAFT = 'DRAFT';
    case APPROVED = 'APPROVED';
    case RECEIVED = 'RECEIVED';
    case CANCELLED = 'CANCELLED';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::DRAFT => in_array($target, [self::APPROVED, self::CANCELLED], true),
            self::APPROVED => in_array($target, [self::RECEIVED, self::CANCELLED], true),
            self::RECEIVED, self::CANCELLED => false,
        };
    }

    public function isImmutable(): bool
    {
        return in_array($this, [self::RECEIVED, self::CANCELLED], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::APPROVED => 'Approved',
            self::RECEIVED => 'Received',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-secondary',
            self::APPROVED => 'bg-primary',
            self::RECEIVED => 'bg-success',
            self::CANCELLED => 'bg-danger',
        };
    }
}
