<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    public function temporary(Media $media): Response
    {
        abort_unless(auth()->check(), 403);

        $disk = Storage::disk($media->disk);

        abort_unless($disk->exists($media->path), 404);

        return $disk->response($media->path, $media->name, [
            'Content-Type' => $media->mime_type,
            'Content-Disposition' => 'inline; filename="'.$media->file_name.'"',
        ]);
    }
}
