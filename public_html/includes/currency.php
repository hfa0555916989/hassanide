<?php
/**
 * Hassan IDE - Currency Service
 * Handles multi-currency pricing based on user location
 */

class CurrencyService {
    private static $instance = null;
    private $currentCurrency = 'USD';
    private $exchangeRates = [];
    private $userCountry = 'US';
    
    // Base prices in USD
    private $basePrices = [
        'pro_monthly' => 7.99,
        'pro_yearly' => 79.90,  // ~17% discount
        'teams_monthly' => 24.99,
        'teams_yearly' => 249.90,  // ~17% discount
        'teams_per_user' => 4.99
    ];
    
    // Currency configurations
    private $currencies = [
        'USD' => [
            'symbol' => '$',
            'name' => 'US Dollar',
            'position' => 'before',
            'decimals' => 2,
            'rate' => 1.0
        ],
        'EUR' => [
            'symbol' => '€',
            'name' => 'Euro',
            'position' => 'before',
            'decimals' => 2,
            'rate' => 0.92
        ],
        'GBP' => [
            'symbol' => '£',
            'name' => 'British Pound',
            'position' => 'before',
            'decimals' => 2,
            'rate' => 0.79
        ],
        'SAR' => [
            'symbol' => 'ر.س',
            'name' => 'Saudi Riyal',
            'position' => 'after',
            'decimals' => 2,
            'rate' => 3.75
        ],
        'AED' => [
            'symbol' => 'د.إ',
            'name' => 'UAE Dirham',
            'position' => 'after',
            'decimals' => 2,
            'rate' => 3.67
        ],
        'EGP' => [
            'symbol' => 'ج.م',
            'name' => 'Egyptian Pound',
            'position' => 'after',
            'decimals' => 0,
            'rate' => 30.90
        ],
        'INR' => [
            'symbol' => '₹',
            'name' => 'Indian Rupee',
            'position' => 'before',
            'decimals' => 0,
            'rate' => 83.00
        ],
        'TRY' => [
            'symbol' => '₺',
            'name' => 'Turkish Lira',
            'position' => 'before',
            'decimals' => 0,
            'rate' => 32.00
        ]
    ];
    
    // Country to currency mapping
    private $countryToCurrency = [
        'US' => 'USD', 'CA' => 'USD',
        'GB' => 'GBP', 'UK' => 'GBP',
        'DE' => 'EUR', 'FR' => 'EUR', 'IT' => 'EUR', 'ES' => 'EUR', 'NL' => 'EUR',
        'SA' => 'SAR', 'KW' => 'SAR', 'QA' => 'SAR', 'BH' => 'SAR', 'OM' => 'SAR',
        'AE' => 'AED',
        'EG' => 'EGP',
        'IN' => 'INR', 'PK' => 'INR', 'BD' => 'INR',
        'TR' => 'TRY'
    ];
    
    private function __construct() {
        $this->detectUserLocation();
        $this->loadCurrencyFromSession();
    }
    
    public static function getInstance(): CurrencyService {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function detectUserLocation(): void {
        // Check if currency is set in URL
        if (isset($_GET['currency']) && isset($this->currencies[$_GET['currency']])) {
            $this->currentCurrency = $_GET['currency'];
            $_SESSION['currency'] = $this->currentCurrency;
            return;
        }
        
        // Try to detect country from IP
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if ($ip && $ip !== '127.0.0.1' && $ip !== '::1') {
            // Try IP geolocation (you can use a service like ip-api.com)
            $geoData = $this->getGeoFromIP($ip);
            if ($geoData && isset($geoData['countryCode'])) {
                $this->userCountry = $geoData['countryCode'];
                $this->currentCurrency = $this->countryToCurrency[$this->userCountry] ?? 'USD';
            }
        }
    }
    
    private function loadCurrencyFromSession(): void {
        if (isset($_SESSION['currency']) && isset($this->currencies[$_SESSION['currency']])) {
            $this->currentCurrency = $_SESSION['currency'];
        }
    }
    
    private function getGeoFromIP(string $ip): ?array {
        // Cache the result to avoid too many API calls
        $cacheKey = 'geo_' . md5($ip);
        if (isset($_SESSION[$cacheKey])) {
            return $_SESSION[$cacheKey];
        }
        
        try {
            $response = @file_get_contents("http://ip-api.com/json/{$ip}?fields=countryCode,country");
            if ($response) {
                $data = json_decode($response, true);
                $_SESSION[$cacheKey] = $data;
                return $data;
            }
        } catch (Exception $e) {
            // Ignore errors, default to USD
        }
        
        return null;
    }
    
    public function getCurrentCurrency(): string {
        return $this->currentCurrency;
    }
    
    public function setCurrency(string $currency): void {
        if (isset($this->currencies[$currency])) {
            $this->currentCurrency = $currency;
            $_SESSION['currency'] = $currency;
        }
    }
    
    public function getCurrencyConfig(): array {
        return $this->currencies[$this->currentCurrency];
    }
    
    public function getAllCurrencies(): array {
        return $this->currencies;
    }
    
    public function getPrice(string $priceKey): float {
        $basePrice = $this->basePrices[$priceKey] ?? 0;
        $rate = $this->currencies[$this->currentCurrency]['rate'];
        return $basePrice * $rate;
    }
    
    public function formatPrice(float $amount): string {
        $config = $this->currencies[$this->currentCurrency];
        $formatted = number_format($amount, $config['decimals']);
        
        if ($config['position'] === 'before') {
            return $config['symbol'] . $formatted;
        } else {
            return $formatted . ' ' . $config['symbol'];
        }
    }
    
    public function getPriceFormatted(string $priceKey): string {
        return $this->formatPrice($this->getPrice($priceKey));
    }
    
    public function getPricesForPlan(string $plan): array {
        $prices = [];
        
        switch ($plan) {
            case 'starter':
                $prices = [
                    'monthly' => 0,
                    'yearly' => 0,
                    'monthly_formatted' => $this->formatFree(),
                    'yearly_formatted' => $this->formatFree()
                ];
                break;
                
            case 'pro':
                $prices = [
                    'monthly' => $this->getPrice('pro_monthly'),
                    'yearly' => $this->getPrice('pro_yearly'),
                    'monthly_formatted' => $this->getPriceFormatted('pro_monthly'),
                    'yearly_formatted' => $this->getPriceFormatted('pro_yearly'),
                    'savings_percent' => 17
                ];
                break;
                
            case 'teams':
                $prices = [
                    'monthly' => $this->getPrice('teams_monthly'),
                    'yearly' => $this->getPrice('teams_yearly'),
                    'monthly_formatted' => $this->getPriceFormatted('teams_monthly'),
                    'yearly_formatted' => $this->getPriceFormatted('teams_yearly'),
                    'per_user' => $this->getPrice('teams_per_user'),
                    'per_user_formatted' => $this->getPriceFormatted('teams_per_user'),
                    'savings_percent' => 17
                ];
                break;
        }
        
        return $prices;
    }
    
    private function formatFree(): string {
        $lang = Language::getInstance();
        return $lang->get('pricing_free');
    }
    
    public function getCurrencySelector(): string {
        $current = $this->currentCurrency;
        $html = '<select id="currencySelector" class="currency-selector" onchange="changeCurrency(this.value)">';
        
        foreach ($this->currencies as $code => $config) {
            $selected = $code === $current ? 'selected' : '';
            $html .= "<option value=\"{$code}\" {$selected}>{$config['symbol']} {$code}</option>";
        }
        
        $html .= '</select>';
        return $html;
    }
}

// Helper function
function currency(): CurrencyService {
    return CurrencyService::getInstance();
}
