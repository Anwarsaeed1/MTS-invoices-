<?php

echo "🌐 Testing Web API Endpoint\n";
echo "===========================\n\n";

// Test the API endpoint
$url = 'http://localhost:8000/api/invoices';

echo "📡 Testing URL: {$url}\n\n";

try {
    // Use PHP's built-in HTTP client
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Content-Type: application/json',
            'timeout' => 10
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    
    if ($response === false) {
        echo "❌ Failed to connect to API endpoint\n";
        echo "   Make sure the server is running: php -S localhost:8000 -t public\n";
        exit(1);
    }
    
    // Parse JSON response
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "❌ Invalid JSON response:\n";
        echo $response . "\n";
        exit(1);
    }
    
    echo "✅ API Response Success!\n";
    echo "📊 Total invoices: " . count($data) . "\n\n";
    
    if (!empty($data)) {
        echo "📄 Sample Invoice Data:\n";
        $sample = $data[0];
        echo "   ID: " . ($sample['id'] ?? 'N/A') . "\n";
        echo "   Date: " . ($sample['date'] ?? 'N/A') . "\n";
        echo "   Customer: " . ($sample['customer']['name'] ?? 'N/A') . "\n";
        echo "   Total: $" . ($sample['grand_total'] ?? 'N/A') . "\n\n";
        
        echo "📄 First 3 Invoices:\n";
        for ($i = 0; $i < min(3, count($data)); $i++) {
            $invoice = $data[$i];
            echo "   Invoice " . ($i + 1) . ":\n";
            echo "     ID: " . ($invoice['id'] ?? 'N/A') . "\n";
            echo "     Date: " . ($invoice['date'] ?? 'N/A') . "\n";
            echo "     Customer: " . ($invoice['customer']['name'] ?? 'N/A') . "\n";
            echo "     Total: $" . ($invoice['grand_total'] ?? 'N/A') . "\n";
            echo "\n";
        }
        
        // Test individual invoice endpoint
        $firstInvoiceId = $data[0]['id'] ?? 1;
        echo "🔍 Testing individual invoice endpoint (ID: {$firstInvoiceId}):\n";
        
        $detailUrl = "http://localhost:8000/api/invoices/{$firstInvoiceId}";
        $detailResponse = file_get_contents($detailUrl, false, $context);
        
        if ($detailResponse !== false) {
            $detailData = json_decode($detailResponse, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "   ✅ Individual invoice endpoint working!\n";
                echo "   Customer: " . ($detailData['customer']['name'] ?? 'N/A') . "\n";
                echo "   Address: " . ($detailData['customer']['address'] ?? 'N/A') . "\n";
            } else {
                echo "   ❌ Invalid JSON in individual invoice response\n";
            }
        } else {
            echo "   ❌ Failed to get individual invoice\n";
        }
        
    } else {
        echo "❌ No invoices found in API response\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🎉 API Testing Complete!\n";
