<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;

class ImportUsersCommand extends Command implements PromptsForMissingInput
{
    protected $signature = 'import:users {file : The path to the JSON file}';

    protected $description = 'Import users and their purchase histories from a JSON file';

    public function handle(): void
    {
        // 取得 JSON 檔案路徑
        $filePath = $this->argument('file');

        // 檢查檔案是否存在
        if (! File::exists($filePath)) {
            $this->fail("檔案不存在: $filePath");
        }

        // 讀取並解析 JSON 檔案
        $users = File::json($filePath);
        if (! $users) {
            $this->fail('解析 JSON 檔案失敗');
        }

        try {
            DB::transaction(function () use ($users) {
                foreach ($users as $userData) {
                    // 新增使用者
                    $user = User::create([
                        'name' => $userData['name'],
                        'cash_balance' => $userData['cashBalance'],
                    ]);

                    // 若有購買紀錄則新增購買紀錄
                    if (! empty($userData['purchaseHistories'])) {
                        $user->purchaseHistories()->createMany(array_map(function ($item) {
                            return [
                                'pharmacy_name' => $item['pharmacyName'],
                                'mask_name' => $item['maskName'],
                                'transaction_amount' => $item['transactionAmount'],
                                'transaction_quantity' => $item['transactionQuantity'],
                                'transaction_datetime' => $item['transactionDatetime'],
                            ];
                        }, $userData['purchaseHistories']));
                    }
                }
            });
            $this->info('使用者與購買紀錄導入成功');
        } catch (Throwable $e) {
            $this->fail('導入資料失敗: '.$e->getMessage());
        }
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
