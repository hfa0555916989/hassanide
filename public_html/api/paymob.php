<?php
/**
 * Hassan IDE - PayMob Integration
 * ================================
 * التعامل مع PayMob API للدفع الإلكتروني
 */

define('HASSAN_IDE', true);
require_once __DIR__ . '/config.php';

class PayMobAPI {
    
    private $apiKey;
    private $secretKey;
    private $publicKey;
    private $integrationId;
    private $baseUrl = 'https://ksa.paymob.com/api';
    
    public function __construct() {
        $this->apiKey = PAYMOB_API_KEY;
        $this->secretKey = PAYMOB_SECRET_KEY;
        $this->publicKey = PAYMOB_PUBLIC_KEY;
        $this->integrationId = PAYMOB_INTEGRATION_CARD;
    }
    
    /**
     * الخطوة 1: الحصول على Auth Token
     */
    public function getAuthToken() {
        $response = $this->makeRequest('/auth/tokens', [
            'api_key' => $this->apiKey
        ]);
        
        if (isset($response['token'])) {
            return $response['token'];
        }
        
        throw new Exception('Failed to get auth token: ' . json_encode($response));
    }
    
    /**
     * الخطوة 2: إنشاء Order
     */
    public function createOrder($authToken, $amount, $currency = 'SAR', $items = []) {
        $response = $this->makeRequest('/ecommerce/orders', [
            'auth_token' => $authToken,
            'delivery_needed' => false,
            'amount_cents' => $amount * 100, // تحويل لهللات
            'currency' => $currency,
            'items' => $items
        ]);
        
        if (isset($response['id'])) {
            return $response;
        }
        
        throw new Exception('Failed to create order: ' . json_encode($response));
    }
    
    /**
     * الخطوة 3: إنشاء Payment Key
     */
    public function createPaymentKey($authToken, $orderId, $amount, $billingData, $currency = 'SAR') {
        $response = $this->makeRequest('/acceptance/payment_keys', [
            'auth_token' => $authToken,
            'amount_cents' => $amount * 100,
            'expiration' => 3600, // ساعة واحدة
            'order_id' => $orderId,
            'billing_data' => $billingData,
            'currency' => $currency,
            'integration_id' => (int)$this->integrationId,
            'lock_order_when_paid' => true
        ]);
        
        if (isset($response['token'])) {
            return $response['token'];
        }
        
        throw new Exception('Failed to create payment key: ' . json_encode($response));
    }
    
    /**
     * دالة شاملة لإنشاء عملية دفع جديدة
     */
    public function initiatePayment($amount, $user, $plan, $billingCycle = 'monthly') {
        try {
            // الخطوة 1: Auth Token
            $authToken = $this->getAuthToken();
            
            // الخطوة 2: Create Order
            $items = [
                [
                    'name' => "Hassan IDE - {$plan['name']} ({$billingCycle})",
                    'amount_cents' => $amount * 100,
                    'quantity' => 1
                ]
            ];
            $order = $this->createOrder($authToken, $amount, 'SAR', $items);
            
            // الخطوة 3: Payment Key
            $billingData = [
                'first_name' => $user['name'] ?? 'Customer',
                'last_name' => '',
                'email' => $user['email'],
                'phone_number' => $user['phone'] ?? '+966500000000',
                'apartment' => 'NA',
                'floor' => 'NA',
                'street' => 'NA',
                'building' => 'NA',
                'shipping_method' => 'NA',
                'postal_code' => 'NA',
                'city' => 'Riyadh',
                'country' => 'SA',
                'state' => 'NA'
            ];
            
            $paymentKey = $this->createPaymentKey(
                $authToken, 
                $order['id'], 
                $amount, 
                $billingData
            );
            
            return [
                'success' => true,
                'order_id' => $order['id'],
                'payment_key' => $paymentKey,
                'iframe_url' => "https://ksa.paymob.com/api/acceptance/iframes/19892?payment_token={$paymentKey}"
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * التحقق من صحة Callback من PayMob
     */
    public function verifyCallback($data, $hmac) {
        // البيانات المطلوبة للتحقق
        $fields = [
            'amount_cents',
            'created_at',
            'currency',
            'error_occured',
            'has_parent_transaction',
            'id',
            'integration_id',
            'is_3d_secure',
            'is_auth',
            'is_capture',
            'is_refunded',
            'is_standalone_payment',
            'is_voided',
            'order',
            'owner',
            'pending',
            'source_data_pan',
            'source_data_sub_type',
            'source_data_type',
            'success'
        ];
        
        $concatenated = '';
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $concatenated .= $data[$field];
            }
        }
        
        $calculatedHmac = hash_hmac('sha512', $concatenated, $this->secretKey);
        
        return hash_equals($calculatedHmac, $hmac);
    }
    
    /**
     * إجراء طلب HTTP
     */
    private function makeRequest($endpoint, $data) {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("CURL Error: {$error}");
        }
        
        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response: {$response}");
        }
        
        return $decoded;
    }
}

// ===========================================
// معالجة طلبات API
// ===========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    $paymob = new PayMobAPI();
    
    switch ($action) {
        case 'create_payment':
            $user = [
                'name' => $input['name'] ?? '',
                'email' => $input['email'] ?? '',
                'phone' => $input['phone'] ?? ''
            ];
            $plan = getPlan($input['plan'] ?? 'pro');
            $billingCycle = $input['billing_cycle'] ?? 'monthly';
            $amount = $billingCycle === 'yearly' ? $plan['price_yearly'] : $plan['price_monthly'];
            
            $result = $paymob->initiatePayment($amount, $user, $plan, $billingCycle);
            
            // حفظ في قاعدة البيانات
            if ($result['success']) {
                try {
                    $db = getDB();
                    $stmt = $db->prepare("
                        INSERT INTO payments (user_id, paymob_order_id, amount, status, metadata)
                        VALUES (?, ?, ?, 'pending', ?)
                    ");
                    $stmt->execute([
                        $input['user_id'] ?? null,
                        $result['order_id'],
                        $amount,
                        json_encode([
                            'plan' => $input['plan'],
                            'billing_cycle' => $billingCycle
                        ])
                    ]);
                } catch (Exception $e) {
                    // Log error but don't fail the payment
                    error_log("DB Error: " . $e->getMessage());
                }
            }
            
            echo json_encode($result);
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
    exit;
}
