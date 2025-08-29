<?php

namespace App\Traits;

trait HasToastNotifications
{
    protected function dispatchSuccessToast(string $message): void
    {
        $this->dispatch('toast', message: $message, type: 'success');
    }

    protected function dispatchErrorToast(string $message): void
    {
        $this->dispatch('toast', message: $message, type: 'error');
    }

    protected function dispatchWarningToast(string $message): void
    {
        $this->dispatch('toast', message: $message, type: 'warning');
    }

    protected function dispatchInfoToast(string $message): void
    {
        $this->dispatch('toast', message: $message, type: 'info');
    }
}
