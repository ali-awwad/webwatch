<?php

namespace App\Filament\Resources\TechStackResource\Pages;

use App\Filament\Resources\TechStackResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTechStack extends EditRecord
{
    protected static string $resource = TechStackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
} 