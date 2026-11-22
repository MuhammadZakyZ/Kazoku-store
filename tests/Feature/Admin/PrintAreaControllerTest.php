<?php

namespace Tests\Feature\Admin;

use App\Support\AreaRules;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\ShopTestCase;

class PrintAreaControllerTest extends ShopTestCase
{
    public function test_admin_editor_and_mockup_route_render_the_back_side(): void
    {
        Storage::fake('local');
        $admin = $this->admin();
        $shop = $this->shop();
        $path = 'mockups/black-back.png';

        Storage::disk('local')->put($path, 'back-mockup');
        $shop['black']->update(['mockup_back' => $path]);
        $shop['back']->update(['side' => 'belakang']);

        $this->actingAs($admin)
            ->get(route('admin.products.edit', $shop['product']))
            ->assertOk()
            ->assertSee('Area depan')
            ->assertSee('Area belakang')
            ->assertSee(route('media.mockup', [$shop['black'], 'belakang']), false);

        $response = $this->get(route('media.mockup', [$shop['black'], 'belakang']));

        $response->assertOk();
        $this->assertSame('back-mockup', $response->getContent());
    }

    public function test_admin_can_create_a_back_area_with_conflicts_and_artwork(): void
    {
        Storage::fake('local');
        $admin = $this->admin();
        $shop = $this->shop();

        $this->actingAs($admin)->post(route('admin.products.areas.store', $shop['product']), [
            'name' => 'Full Belakang',
            'side' => 'belakang',
            'width_cm' => 30,
            'height_cm' => 40,
            'price' => 0,
            'conflicts' => [$shop['chest']->id],
            'artwork_file' => UploadedFile::fake()->create('full-back.pdf', 100, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $area = $shop['product']->printAreas()->where('name', 'Full Belakang')->firstOrFail();

        $this->assertSame('belakang', $area->side->value);
        $this->assertDatabaseHas('print_area_conflicts', [
            'print_area_id' => min($area->id, $shop['chest']->id),
            'conflicts_with_id' => max($area->id, $shop['chest']->id),
        ]);
        Storage::disk('local')->assertExists($area->artwork_file);
        $this->assertContains($shop['chest']->id, AreaRules::map($shop['product']->id)[$area->id]);
    }

    public function test_admin_can_remove_area_artwork_and_conflict_rules(): void
    {
        Storage::fake('local');
        $admin = $this->admin();
        $shop = $this->shop();
        $area = $shop['back'];
        $area->update([
            'side' => 'belakang',
            'artwork_file' => 'print-areas/back.pdf',
        ]);
        Storage::disk('local')->put($area->artwork_file, 'sample');
        AreaRules::setConflicts($area, [$shop['chest']->id]);

        $this->actingAs($admin)->put(route('admin.areas.update', $area), [
            'name' => $area->name,
            'side' => 'belakang',
            'width_cm' => $area->width_cm,
            'height_cm' => $area->height_cm,
            'price' => $area->price,
            'remove_artwork_file' => '1',
        ])->assertSessionHasNoErrors();

        $area->refresh();
        $this->assertNull($area->artwork_file);
        Storage::disk('local')->assertMissing('print-areas/back.pdf');
        $this->assertDatabaseMissing('print_area_conflicts', [
            'print_area_id' => min($area->id, $shop['chest']->id),
            'conflicts_with_id' => max($area->id, $shop['chest']->id),
        ]);
    }
}