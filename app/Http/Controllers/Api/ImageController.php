<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessImageDelete;
use App\Jobs\ProcessImageThumbnails;
use App\Models\Image;
use Illuminate\Bus\Dispatcher;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Model::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $this->validate($request, [
            'image' => 'required|mimes:jpeg,jpg,png|max:10000|dimensions:max_width=2000,max_height=2000',
            'width' => 'numeric|required_without:height',
            'height' => 'numeric',
        ]);

        $image = $request->file('image');
        $newImageName = uniqid();
        $ext = $image->getClientOriginalExtension();


        // Сохраняем изображение в хранилище
        $res = $image->storeAs('api/img/', $newImageName . '.' . $ext);

        // Сохраняем данные в модели
        $imgObject = Image::create([
            'name' => $newImageName,
            'type' => 'api',
            'format' => $ext,
        ]);

        // Make thumbnail through the queue
        $queueId = app(Dispatcher::class)->dispatch(
            (new ProcessImageThumbnails(
                $imgObject,
                $request->input('width'),
                $request->input('height')
            ))
        );

        // Deleting image after one hour
        ProcessImageDelete::dispatch($imgObject)->delay(10);

        $imgObject->queue = $queueId;
        $imgObject->save();

        // Запустить удаление спустя час

        return response()->json($queueId, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Image $image)
    {
        if ($image->status) {
            return response(json_encode(asset('storage/api/img/thumbs/' . $image->name . '.' . $image->format), JSON_UNESCAPED_SLASHES), 201);
        }

        return response('image processing');

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Model $unit)
    {
        if ($unit->delete()) {
            return response()->json(null, 204);
        }

        return response()->json(null, 404);
    }
}
