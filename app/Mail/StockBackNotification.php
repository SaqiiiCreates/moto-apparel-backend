<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Webkul\Marketing\Repositories\TemplateRepository;

class StockBackNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $waitlist;

    public function __construct($waitlist)
    {
        $this->waitlist = $waitlist;
    }

    public function build()
    {
        $repo = app(TemplateRepository::class);
        $templateId = trim((string) core()->getConfigData('emails.general.waitlist.template_id'));

        $template = $templateId !== ''
            ? $repo->find($templateId)
            : $repo->findOneWhere([
                'name'   => 'Stock Back Notification',
                'status' => 'active',
            ]);

        if (! $template || $template->status !== 'active') {
            throw new \RuntimeException('Active waitlist email template was not found.');
        }

        return $this
            ->subject('Good News! Your size is back in stock')
            ->html($this->renderTemplate($template->content));
    }

    private function renderTemplate(string $content): string
    {
        $productId = (string) ($this->waitlist->product_variant_id ?: $this->waitlist->product_id ?: '');
        $shopUrl = url('/products');

        return strtr($content, [
            '[[product_id]]' => $productId,
            '[[shop_url]]'   => $shopUrl,
            '{{product_id}}' => $productId,
            '{{shop_url}}'   => $shopUrl,
        ]);
    }
}