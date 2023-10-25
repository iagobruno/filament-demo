<x-filament-panels::page>
    <x-filament-panels::form
        :wire:key="$this->getId() . '.forms.' . $this->getFormStatePath()"
        wire:submit="save">

        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()" />
    </x-filament-panels::form>

    <style>
        .fi-sidebar-nav { display: none }
    </style>
</x-filament-panels::page>
