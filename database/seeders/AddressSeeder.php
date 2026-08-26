<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Models\Region;
use App\Models\Province;
use App\Models\City;
use App\Models\Barangay;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        $publication = config('address.publication.path');
        $sheet = config('address.publication.sheet', 4);

        if (!file_exists($publication)) {
            $this->command->error("File not found: {$publication}");
            return;
        }

        $regions = [];
        $provinces = [];
        $cities = [];
        $barangays = [];

        (new FastExcel)
            ->sheet($sheet)
            ->import($publication, function ($line) use (&$regions, &$provinces, &$cities, &$barangays) {
                
                $rawCode = (string) ($line['10-digit PSGC'] ?? '');
                if (empty($rawCode)) return;

                // Force 10-digit zero-padded code (fixes Excel number stripping)
                $code = str_pad($rawCode, 10, '0', STR_PAD_LEFT);

                $attributes = [
                    'code'                => $code,
                    'correspondence_code' => (string) ($line['Correspondence Code'] ?? ''),
                    'name'                => trim((string) ($line['Name'] ?? '')),
                    'region_id'           => substr($code, 0, 2),
                ];

                $geoLevel  = trim((string) ($line['Geographic Level'] ?? ''));
                $cityClass = trim((string) ($line['City Class'] ?? ''));

                if ($this->hasOwnProvince($geoLevel, $attributes['region_id'], $cityClass)) {
                    $provinces[] = [
                        ...$attributes,
                        'province_id' => substr($code, 0, 5),
                        'name'        => trim((string) ($line['Name'] ?? '')),
                    ];
                }

                switch ($geoLevel) {
                    case 'Reg':
                        $regions[] = $attributes;
                        break;

                    case 'Dist':
                    case 'Prov':
                    case '':
                        $provinces[] = [
                            ...$attributes,
                            'province_id' => substr($code, 0, 5),
                        ];
                        break;

                    case 'Bgy':
                        $barangays[] = [
                            ...$attributes,
                            'province_id' => substr($code, 0, 5),
                            'city_id'     => substr($code, 0, 7),
                        ];
                        break;

                    default: // City, SubMun, Mun
                        if ($code === '1380600000') break;

                        $cities[] = [
                            ...$attributes,
                            'province_id' => substr($code, 0, 5),
                            'city_id'     => substr($code, 0, 7),
                            'name'        => str_replace('City of ', '', $attributes['name']),
                        ];
                        break;
                }
            });

        Region::query()->insert($regions);

        foreach (array_chunk($provinces, 500) as $chunk) {
            Province::query()->insert($chunk);
        }

        foreach (array_chunk($cities, 500) as $chunk) {
            City::query()->insert($chunk);
        }

        foreach (array_chunk($barangays, 500) as $chunk) {
            Barangay::query()->insert($chunk);
        }

        $this->command->info("Seeded: " . count($regions) . " regions, " . count($provinces) . " provinces, " . count($cities) . " cities, " . count($barangays) . " barangays.");
    }

    protected function isMunicipalityInNCR(string $geo, string $regionId): bool
    {
        return in_array($geo, ['City', 'Mun']) && $regionId === '13';
    }

    protected function isHUC(string $geo, string $cityClass): bool
    {
        return $geo === 'City' && $cityClass === 'HUC';
    }

    protected function hasOwnProvince(string $geo, string $regionId, string $cityClass): bool
    {
        return $this->isMunicipalityInNCR($geo, $regionId) || $this->isHUC($geo, $cityClass);
    }
}