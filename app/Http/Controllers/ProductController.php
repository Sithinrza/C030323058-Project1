<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Storage; // Corrected namespace
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index() : View
    {
        $products = Product::latest()->paginate(10);
        return view('products.index', compact('products'));
    }

    public function create() : View
    {
        return view('products.create');
    }

    /**
     * Store a newly created product in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // Validasi form
        $request->validate([
            'image'       => 'required|image|mimes:jpeg,jpg,png', // |max:2048
            'title'       => 'required|min:5',
            'description' => 'required|min:10',
            'price'       => 'required|numeric',
            'stock'       => 'required|numeric'
        ]);

        // Upload image
        $image = $request->file('image');
        $image->storeAs('public/products', $image->hashName());

        // Create product
        Product::create([
            'image'       => $image->hashName(),
            'title'       => $request->title,
            'description' => $request->description,
            'price'       => $request->price,
            'stock'       => $request->stock
        ]);

        // Redirect to index
        return redirect()->route('products.index')->with(['success' => 'Data Berhasil disimpan!']);
    }

    public function show(string $id):View
    {
        $product = Product::findOrFail($id);
        return view('products.show', compact('product'));

    }

    public function edit(string $id): View
    {
        $product = Product::findOrFail($id);
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified product in storage.
     *
     * @param Request $request
     * @param string $id
     * @return RedirectResponse
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        // Validasi form
        $request->validate([
            'image'       => 'image|mimes:jpeg,jpg,png', // Tidak harus 'required' pada update
            'title'       => 'required|min:5',
            'description' => 'required|min:10',
            'price'       => 'required|numeric',
            'stock'       => 'required|numeric'
        ]);

        // Get product by id
        $product = Product::findOrFail($id);

        if ($request->hasFile('image')) {
            // Upload new image
            $image = $request->file('image');
            $image->storeAs('public/products', $image->hashName());

            // Delete old image
            Storage::delete('public/products/' . $product->image);

            // Update product with new image
            $product->update([
                'image'       => $image->hashName(),
                'title'       => $request->title,
                'description' => $request->description,
                'price'       => $request->price,
                'stock'       => $request->stock
            ]);
        } else {
            // Update product without changing the image
            $product->update([
                'title'       => $request->title,
                'description' => $request->description,
                'price'       => $request->price,
                'stock'       => $request->stock
            ]);
        }

        // Redirect to index
        return redirect()->route('products.index')->with(['success' => 'Data Berhasil Diubah!']);
    }

    public function destroy($id): RedirectResponse
    {
        //get product by ID
        $product = Product::findOrFail($id);

        Storage::delete('public/products/'. $product->image);

        $product->delete();

        return redirect()->route('products.index')->with(['success' => 'Data Berhasil Dihapus']);

    }

}
