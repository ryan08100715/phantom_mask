<?php

namespace App\Console\Commands;

use App\Enums\DayOfWeek;
use App\Models\Pharmacy;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;

class ImportPharmaciesCommand extends Command implements PromptsForMissingInput
{
    protected $signature = 'import:pharmacies {file : The path to the JSON file}';

    protected $description = 'Import pharmacies, their opening hours, and masks from a JSON file';

    public function handle(): void
    {
        // 取得 JSON 檔案路徑
        $filePath = $this->argument('file');

        // 檢查檔案是否存在
        if (! File::exists($filePath)) {
            $this->fail("檔案不存在: $filePath");
        }

        // 讀取並解析 JSON 檔案
        $pharmacies = File::json($filePath);
        if (! $pharmacies) {
            $this->fail('解析 JSON 檔案失敗');
        }

        try {
            DB::transaction(function () use ($pharmacies) {
                foreach ($pharmacies as $pharmacyData) {
                    // 新增藥局
                    $pharmacy = Pharmacy::create([
                        'name' => $pharmacyData['name'],
                        'cash_balance' => $pharmacyData['cashBalance'],
                    ]);

                    // 解析藥局營業時間並新增
                    $openingHours = $this->parseOpeningHours($pharmacyData['openingHours']);
                    $pharmacy->openingHours()->createMany($openingHours);

                    // 若有口罩資訊則新增口罩資訊
                    if (! empty($pharmacyData['masks'])) {
                        $pharmacy->masks()->createMany(array_map(function ($item) {
                            return [
                                'name' => $item['name'],
                                'price' => $item['price'],
                                'stock_quantity' => $item['stockQuantity'],
                            ];
                        }, $pharmacyData['masks']));
                    }
                }
            });
            $this->info('藥局與營業時間與口罩資訊導入成功');
        } catch (Throwable $e) {
            $this->fail('導入資料失敗: '.$e->getMessage());
        }
    }

    /**
     * Parse opening hours string into an array of day_of_week, start_time, and end_time.
     *
     * @return list<array{
     *    day_of_week: DayOfWeek,
     *    start_time: string,
     *    end_time: string,
     *  }>
     *
     * @throws Throwable
     */
    protected function parseOpeningHours(string $openingHours): array
    {
        $dayMap = [
            'Mon' => DayOfWeek::Monday,
            'Tue' => DayOfWeek::Tuesday,
            'Wed' => DayOfWeek::Wednesday,
            'Thur' => DayOfWeek::Thursday,
            'Fri' => DayOfWeek::Friday,
            'Sat' => DayOfWeek::Saturday,
            'Sun' => DayOfWeek::Sunday,
        ];

        $result = [];
        $hoursArray = array_map('trim', explode(',', $openingHours)); // 使用 , 分割字串，並移除左右空白

        foreach ($hoursArray as $hours) {
            // Match pattern like "Mon 08:00 - 18:00"
            if (preg_match('/^(\w+)\s+(\d{2}:\d{2})\s+-\s+(\d{2}:\d{2})$/', $hours, $matches)) {
                $dayOfWeek = $dayMap[$matches[1]] ?? $matches[1];
                $startTime = $matches[2];
                $endTime = $matches[3];

                // 驗證 dayOfWeek 格式
                if (! $dayOfWeek instanceof DayOfWeek) {
                    $this->fail("Invalid day of week: $dayOfWeek in entry: $hours");
                }

                $result[] = [
                    'day_of_week' => $dayOfWeek,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ];
            } else {
                $this->fail("Invalid opening hours entry: $hours");
            }
        }

        return $result;
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array<string, string>
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'file' => 'The path to the JSON file',
        ];
    }
}
