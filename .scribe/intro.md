# Introduction



<aside>
    <strong>Base URL</strong>: <code>http://localhost:8000</code>
</aside>

<h1>響應錯誤處理說明</h1>

<h2>錯誤格式</h2>

```json
{
  "message": "錯誤描述",
  "errors": [
    {
      "code": "錯誤代碼",
      "detail": "該錯誤描述"
    }

  ]
}
```

<aside>當有錯誤發生時，會包含 errors 參數，errors 可能包含多個 error，每個 error 都有 code 可以進行程式判斷。</aside>

<h2>錯誤代碼清單</h2>

|  code   | 描述  |
|  ----  | ----  |
| resource_not_found  | 查無資源 |
| invalid_format  | 參數格式錯誤 |
| insufficient_cash_balance  | 現金餘額不足 |
| insufficient_stock  | 庫存不足 |
| server_error  | 未知錯誤 |


