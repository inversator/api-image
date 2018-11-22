<?php

namespace App\Jobs;

use App\Models\Image;
use App\Models\Image as ImageModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessImageThumbnails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    protected $image, $height, $width;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ImageModel $image, $width, $height)
    {
        $this->image = $image;
        $this->height = $height;
        $this->width = $width;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $image = $this->image;

        $imageName = $image->name . '.' . $image->format;

        $img = \ImageMaster::make(storage_path('app/api/img/' . $imageName))->resize(
            $this->width,
            $this->height,
            function ($constraint) {
                $constraint->aspectRatio();
            }
        );

        Storage::put('public/api/img/thumbs/' . $imageName, (string)$img->encode());

        $image->status = 1;
        $image->save();

    }

}
