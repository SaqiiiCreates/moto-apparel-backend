<?php

namespace Webkul\Shop\Http\Controllers\API;

use Illuminate\Http\Request;
use Webkul\Shop\Http\Controllers\Controller;
use Webkul\Product\Repositories\ProductWaitlistRepository;

class ProductWaitlistController extends Controller
{
    protected ProductWaitlistRepository $productWaitlistRepository;

    public function __construct(ProductWaitlistRepository $productWaitlistRepository)
    {
        $this->productWaitlistRepository = $productWaitlistRepository;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_variant_id' => ['required', 'exists:products,id'],
            'email' => ['required', 'email'],
        ]);

        $waitlist = $this->productWaitlistRepository->findOneWhere([
            'product_variant_id' => $validated['product_variant_id'],
            'email'              => $validated['email'],
        ]);
        
        if ($waitlist) {
            return response()->json([
                'message' => 'You are already on the waitlist for this variant.'
            ]);
        }

        if (! $waitlist) {
            $waitlist = $this->productWaitlistRepository->create([
                'product_variant_id' => $validated['product_variant_id'],
                'email'              => $validated['email'],
                'customer_id'        => auth('customer')->id(),
                'is_notified'        => false,
            ]);
        }

        return response()->json([
            'message' => 'You have been added to the waitlist.',
            'data'    => $waitlist,
        ], 201);
    }
}