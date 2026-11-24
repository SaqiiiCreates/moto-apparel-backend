<?php

namespace Webkul\Product\Observers;

use Webkul\Product\Models\ProductInventory;
use App\Mail\StockBackNotification;
use Illuminate\Support\Facades\Mail;
use Webkul\Product\Repositories\ProductWaitlistRepository;

class ProductWaitlistObserver
{
    protected $waitlistRepository;

    public function __construct(ProductWaitlistRepository $productWaitlistRepository)
    {
        $this->waitlistRepository = $productWaitlistRepository;
    }

    /**
     * Handle the ProductInventory "updated" event.
     */
    public function updated(ProductInventory $inventory)
    {
        // Trigger only when stock becomes available (qty > 0)
        if ($inventory->qty > 0) {
            $this->notifyWaitingCustomers($inventory);
        }
    }

    private function notifyWaitingCustomers(ProductInventory $inventory)
    {
        $ids = array_filter([
            $inventory->product_id,
            $inventory->product?->parent_id,
        ]);

        // \Log::info('Waitlist observer ran', [
        //     'inventory_product_id' => $inventory->product_id,
        //     'parent_id'            => $inventory->product?->parent_id,
        //     'looking_for'          => $ids,
        //     'qty'                  => $inventory->qty,
        // ]);

        $waitlists = $this->waitlistRepository
            ->getModel()
            ->whereIn('product_variant_id', $ids)
            ->where('is_notified', 0)
            ->get();

        // \Log::info('Found waitlist entries', ['count' => $waitlists->count()]);

        foreach ($waitlists as $entry) {
            try {
                Mail::to($entry->email)
                    ->queue(new StockBackNotification($entry));

                $entry->update(['is_notified' => true]);
            } catch (\Exception $e) {
                // \Log::error('Waitlist mail failed: ' . $e->getMessage());
            }
        }
    }
}