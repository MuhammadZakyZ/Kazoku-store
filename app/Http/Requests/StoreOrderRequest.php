<?php

namespace App\Http\Requests;

use App\Enums\DeliveryMethod;
use App\Enums\DesignType;
use App\Enums\PaymentMethod;
use App\Models\PrintArea;
use App\Support\AreaRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // akses dijaga middleware auth + verified (BR-11)
    }

    public function rules(): array
    {
        $product = $this->route('product');

        $rules = [
            'option_id' => [
                Rule::requiredIf($product->options()->exists()),
                'nullable',
                Rule::exists('product_options', 'id')->where('product_id', $product->id),
            ],
            'color_id' => [
                Rule::requiredIf($product->colors()->exists()),
                'nullable',
                Rule::exists('product_colors', 'id')->where('product_id', $product->id),
            ],
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'design_type' => ['required', Rule::in(array_column(DesignType::cases(), 'value'))],

            'delivery_method' => ['required', Rule::in(array_column(DeliveryMethod::cases(), 'value'))],
            // BR-12: tujuan dan alamat wajib bila memilih kirim.
            'courier_id' => [
                'required_if:delivery_method,'.DeliveryMethod::Kirim->value,
                'nullable',
                Rule::exists('couriers', 'id')->where('is_active', true),
            ],
            'shipping_rate_id' => [
                'required_if:delivery_method,'.DeliveryMethod::Kirim->value,
                'nullable',
                Rule::exists('shipping_rates', 'id')->where('courier_id', $this->input('courier_id')),
            ],
            'shipping_address' => [
                'required_if:delivery_method,'.DeliveryMethod::Kirim->value,
                'nullable', 'string', 'min:10', 'max:500',
            ],

            'payment_method' => ['required', Rule::in(array_column(PaymentMethod::cases(), 'value'))],
        ];

        // BR-13: jalur punya desain wajib satu file per area, satu area hanya sekali.
        if ($this->input('design_type') === DesignType::PunyaDesain->value) {
            $rules['area_ids'] = ['required', 'array', 'min:1'];
            $rules['area_ids.*'] = [
                'integer', 'distinct',
                Rule::exists('print_areas', 'id')->where('product_id', $product->id),
            ];

            foreach ($this->selectedAreaIds() as $id) {
                $rules["designs.$id"] = ['required', 'file', 'mimes:png,jpg,jpeg,webp,pdf', 'max:10240'];
            }
        }

        return $rules;
    }

    /** Aturan kombinasi area: pasangan yang tumpang tindih tidak boleh dipilih bersama. */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('design_type') !== DesignType::PunyaDesain->value) {
                return;
            }

            $product = $this->route('product');
            $violations = AreaRules::violations($this->selectedAreaIds(), $product->id);

            if ($violations === []) {
                return;
            }

            $ids = array_unique(array_merge(...$violations));
            $names = PrintArea::whereIn('id', $ids)->pluck('name', 'id');

            foreach ($violations as [$a, $b]) {
                $validator->errors()->add(
                    'area_ids',
                    "Area {$names[$a]} tidak dapat dipilih bersama {$names[$b]} karena tumpang tindih."
                );
            }
        });
    }

    public function attributes(): array
    {
        $attributes = [
            'option_id' => 'ukuran',
            'color_id' => 'warna',
            'quantity' => 'jumlah',
            'design_type' => 'jenis desain',
            'area_ids' => 'area sablon',
            'delivery_method' => 'metode pengambilan',
            'courier_id' => 'ekspedisi',
            'shipping_rate_id' => 'tujuan pengiriman',
            'shipping_address' => 'alamat pengiriman',
            'payment_method' => 'metode pembayaran',
        ];

        $ids = $this->selectedAreaIds();
        if ($ids !== []) {
            $names = PrintArea::whereIn('id', $ids)->pluck('name', 'id');
            foreach ($ids as $id) {
                $attributes["designs.$id"] = 'file desain untuk area '.($names[$id] ?? $id);
            }
        }

        return $attributes;
    }

    /** @return array<int, int> */
    private function selectedAreaIds(): array
    {
        $ids = [];
        foreach ((array) $this->input('area_ids', []) as $id) {
            if (is_scalar($id) && ctype_digit((string) $id)) {
                $ids[] = (int) $id;
            }
        }

        return array_values(array_unique($ids));
    }
}
