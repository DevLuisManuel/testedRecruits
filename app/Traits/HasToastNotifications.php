<?php

namespace App\Traits;

trait HasToastNotifications
{
    /**
     * Dispatch success toast notification
     */
    protected function dispatchSuccess(string $message): void
    {
        $this->dispatch('toast', message: $message, type: 'success');
    }

    /**
     * Dispatch error toast notification
     */
    protected function dispatchError(string $message): void
    {
        $this->dispatch('toast', message: $message, type: 'error');
    }

    /**
     * Dispatch warning toast notification
     */
    protected function dispatchWarning(string $message): void
    {
        $this->dispatch('toast', message: $message, type: 'warning');
    }

    /**
     * Dispatch info toast notification
     */
    protected function dispatchInfo(string $message): void
    {
        $this->dispatch('toast', message: $message, type: 'info');
    }

    /**
     * Generic toast dispatch
     */
    protected function dispatchToast(string $message, string $type = 'success'): void
    {
        $this->dispatch('toast', message: $message, type: $type);
    }
}
