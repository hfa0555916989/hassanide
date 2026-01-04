<?php
/**
 * Hassan IDE - Pricing Page
 * Multi-currency support with USD as default
 */
session_start();
$pageTitle = 'Pricing';
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/language.php';
require_once __DIR__ . '/includes/currency.php';
require_once __DIR__ . '/includes/header.php';

$lang = Language::getInstance();
$curr = CurrencyService::getInstance();

// Get prices for all plans
$starterPrices = $curr->getPricesForPlan('starter');
$proPrices = $curr->getPricesForPlan('pro');
$teamsPrices = $curr->getPricesForPlan('teams');
?>

<section class="section" style="padding-top: 120px;">
    <div class="container">
        <div class="section-title">
            <h2><?= $lang->get('pricing_title') ?></h2>
            <p><?= $lang->get('pricing_subtitle') ?></p>
            
            <!-- Currency Selector -->
            <div style="display: flex; align-items: center; justify-content: center; gap: 15px; margin-top: 20px;">
                <label for="currencySelector"><?= $lang->get('select_currency') ?>:</label>
                <?= $curr->getCurrencySelector() ?>
            </div>
            
            <!-- Billing Toggle -->
            <div style="display: flex; align-items: center; justify-content: center; gap: 15px; margin-top: 20px;">
                <span id="monthlyLabel" class="billing-label active"><?= $lang->get('pricing_monthly') ?></span>
                <label class="toggle-switch">
                    <input type="checkbox" id="billingToggle">
                    <span class="toggle-slider"></span>
                </label>
                <span id="yearlyLabel" class="billing-label"><?= $lang->get('pricing_yearly') ?></span>
                <span class="savings-badge"><?= $lang->get('pricing_save', ['percent' => '17']) ?></span>
            </div>
        </div>
        
        <div class="pricing-grid">
            <!-- Starter Plan (Free) -->
            <div class="pricing-card">
                <h3><?= $lang->get('plan_starter') ?></h3>
                <p class="plan-desc"><?= $lang->get('pricing_for_beginners') ?></p>
                <div class="price" id="starter-price">
                    <span class="amount"><?= $lang->get('pricing_free') ?></span>
                </div>
                <p class="billing-period"><?= $lang->get('pricing_forever') ?></p>
                
                <ul class="features-list">
                    <li class="included"><i class="fas fa-check"></i> <?= $lang->get('feature_basic_editor') ?></li>
                    <li class="included"><i class="fas fa-check"></i> <?= $lang->get('feature_extensions_limit', ['count' => '5']) ?></li>
                    <li class="included"><i class="fas fa-check"></i> <?= $lang->get('feature_one_pack') ?></li>
                    <li class="excluded"><i class="fas fa-times"></i> <?= $lang->get('feature_auto_updates') ?></li>
                    <li class="excluded"><i class="fas fa-times"></i> <?= $lang->get('feature_support') ?></li>
                </ul>
                
                <a href="<?= SITE_URL ?>/download.php" class="btn btn-outline btn-block">
                    <?= $lang->get('pricing_free_download') ?>
                </a>
            </div>
            
            <!-- Pro Plan -->
            <div class="pricing-card featured">
                <span class="badge"><?= $lang->get('pricing_most_popular') ?></span>
                <h3><?= $lang->get('plan_pro') ?></h3>
                <p class="plan-desc"><?= $lang->get('pricing_for_professionals') ?></p>
                <div class="price" id="pro-price">
                    <span class="amount" data-monthly="<?= $proPrices['monthly_formatted'] ?>" data-yearly="<?= $proPrices['yearly_formatted'] ?>"><?= $proPrices['monthly_formatted'] ?></span>
                    <span class="period" data-monthly="<?= $lang->get('pricing_per_month') ?>" data-yearly="<?= $lang->get('pricing_per_year') ?>"><?= $lang->get('pricing_per_month') ?></span>
                </div>
                <p class="billing-period savings" id="pro-savings">
                    <?= $lang->get('pricing_save', ['percent' => '17']) ?> <?= $lang->get('pricing_yearly') ?>
                </p>
                
                <ul class="features-list">
                    <li class="included"><i class="fas fa-check"></i> <?= $lang->get('feature_all_starter') ?></li>
                    <li class="included"><i class="fas fa-check"></i> <?= $lang->get('feature_all_packs') ?></li>
                    <li class="included"><i class="fas fa-check"></i> <?= $lang->get('feature_unlimited_extensions') ?></li>
                    <li class="included"><i class="fas fa-check"></i> <?= $lang->get('feature_auto_updates') ?></li>
                    <li class="included"><i class="fas fa-check"></i> <?= $lang->get('feature_hassan_panel') ?></li>
                    <li class="included"><i class="fas fa-check"></i> <?= $lang->get('feature_email_support', ['hours' => '48']) ?></li>
                </ul>
                
                <?php if (isset($currentUser) && $currentUser): ?>
                    <a href="<?= SITE_URL ?>/checkout.php?plan=pro" class="btn btn-primary btn-block">
                        <?= $lang->get('pricing_subscribe') ?>
                    </a>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/register.php?plan=pro" class="btn btn-primary btn-block">
                        <?= $lang->get('pricing_free_trial') ?>
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Teams Plan -->
            <div class="pricing-card">
                <h3><?= $lang->get('plan_teams') ?></h3>
                <p class="plan-desc"><?= $lang->get('pricing_for_teams') ?></p>
                <div class="price" id="teams-price">
                    <span class="amount" data-monthly="<?= $teamsPrices['monthly_formatted'] ?>" data-yearly="<?= $teamsPrices['yearly_formatted'] ?>"><?= $teamsPrices['monthly_formatted'] ?></span>
                    <span class="period" data-monthly="<?= $lang->get('pricing_per_month') ?>" data-yearly="<?= $lang->get('pricing_per_year') ?>"><?= $lang->get('pricing_per_month') ?></span>
                </div>
                <p class="billing-period"><?= $lang->get('pricing_users', ['count' => '5']) ?></p>
                
                <ul class="features-list">
                    <li class="included"><i class="fas fa-check"></i> <?= $lang->get('feature_all_pro') ?></li>
                    <li class="included"><i class="fas fa-check"></i> <?= $lang->get('feature_team_dashboard') ?></li>
                    <li class="included"><i class="fas fa-check"></i> <?= $lang->get('feature_permissions') ?></li>
                    <li class="included"><i class="fas fa-check"></i> <?= $lang->get('feature_priority_support', ['hours' => '24']) ?></li>
                    <li class="included"><i class="fas fa-check"></i> <?= $lang->get('feature_invoice') ?></li>
                </ul>
                
                <?php if (isset($currentUser) && $currentUser): ?>
                    <a href="<?= SITE_URL ?>/checkout.php?plan=teams" class="btn btn-outline btn-block">
                        <?= $lang->get('pricing_subscribe') ?>
                    </a>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/register.php?plan=teams" class="btn btn-outline btn-block">
                        <?= $lang->get('pricing_free_trial') ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Enterprise Section -->
        <div class="enterprise-section" style="margin-top: 60px; text-align: center; padding: 40px; background: var(--gray-100); border-radius: 16px;">
            <h3><?= $lang->get('enterprise_title') ?></h3>
            <p><?= $lang->get('enterprise_desc') ?></p>
            <a href="mailto:enterprise@hassanide.com" class="btn btn-outline" style="margin-top: 20px;">
                <?= $lang->get('enterprise_contact') ?>
            </a>
        </div>
    </div>
</section>

<style>
.pricing-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin-top: 40px;
}

.pricing-card {
    background: white;
    border-radius: 16px;
    padding: 40px 30px;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    position: relative;
    transition: transform 0.3s, box-shadow 0.3s;
}

.pricing-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}

.pricing-card.featured {
    border: 2px solid var(--primary);
    transform: scale(1.05);
}

.pricing-card.featured:hover {
    transform: scale(1.05) translateY(-5px);
}

.pricing-card .badge {
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--primary);
    color: white;
    padding: 5px 20px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.price {
    margin: 20px 0 10px;
}

.price .amount {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--dark);
}

.price .period {
    font-size: 1rem;
    color: var(--gray-500);
}

.billing-period {
    color: var(--gray-500);
    font-size: 0.9rem;
    margin-bottom: 20px;
}

.billing-period.savings {
    color: var(--success);
}

.features-list {
    list-style: none;
    padding: 0;
    margin: 30px 0;
    text-align: left;
}

.features-list li {
    padding: 10px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.features-list li.included i {
    color: var(--success);
}

.features-list li.excluded {
    color: var(--gray-400);
}

.features-list li.excluded i {
    color: var(--gray-400);
}

.toggle-switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 30px;
}

.toggle-switch input {
    display: none;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: var(--gray-300);
    border-radius: 30px;
    transition: 0.3s;
}

.toggle-slider:before {
    position: absolute;
    content: '';
    height: 24px;
    width: 24px;
    left: 3px;
    bottom: 3px;
    background: white;
    border-radius: 50%;
    transition: 0.3s;
}

.toggle-switch input:checked + .toggle-slider {
    background: var(--primary);
}

.toggle-switch input:checked + .toggle-slider:before {
    transform: translateX(30px);
}

.billing-label {
    color: var(--gray-500);
    transition: color 0.3s;
}

.billing-label.active {
    color: var(--dark);
    font-weight: 600;
}

.savings-badge {
    background: var(--success);
    color: white;
    padding: 3px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
}

.currency-selector {
    padding: 8px 16px;
    border: 1px solid var(--gray-300);
    border-radius: 8px;
    background: white;
    font-size: 1rem;
    cursor: pointer;
}
</style>

<script>
// Currency change
function changeCurrency(currency) {
    const url = new URL(window.location.href);
    url.searchParams.set('currency', currency);
    window.location.href = url.toString();
}

// Billing toggle
document.getElementById('billingToggle').addEventListener('change', function() {
    const isYearly = this.checked;
    
    // Update labels
    document.getElementById('monthlyLabel').classList.toggle('active', !isYearly);
    document.getElementById('yearlyLabel').classList.toggle('active', isYearly);
    
    // Update prices
    document.querySelectorAll('.price .amount[data-monthly]').forEach(el => {
        el.textContent = isYearly ? el.dataset.yearly : el.dataset.monthly;
    });
    
    document.querySelectorAll('.price .period[data-monthly]').forEach(el => {
        el.textContent = isYearly ? el.dataset.yearly : el.dataset.monthly;
    });
    
    // Show/hide savings
    document.getElementById('pro-savings').style.display = isYearly ? 'none' : 'block';
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
