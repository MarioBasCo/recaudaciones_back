<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        // Guardar la imagen en la base de datos en formato Base64
        //$imageData = base64_encode($imageData); // $imageData es el contenido de la imagen

        // Obtener la imagen de la base de datos y decodificarla para mostrarla
        $image = Image::first();
        if ($image) {
            $decodedImage = base64_encode($image->image);
            return response()->json($decodedImage)->header('Content-Type', 'image');
        } else {
            return response()->json(['message' => 'No images found'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $imageData = $request->file('image');

        if (!$imageData) {
            return response()->json(['message' => 'No image file provided'], 400);
        }

        // Verificar si el archivo es válido antes de guardar
        if ($imageData->isValid()) {
            // Guardar la imagen en la base de datos o en el sistema de archivos según sea necesario
            // Aquí puedes ajustar el código según tu necesidad

            return response()->json(['message' => 'Image updated successfully']);
        } else {
            return response()->json(['message' => 'Invalid image file'], 400);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
