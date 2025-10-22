<?php
/**
 * پشتیبانی از Elementor و ویجت‌های سفارشی
 *
 * @package CryptoSekhyab
 * 
 * توجه: این فایل فقط زمانی بارگذاری می‌شود که Elementor نصب و فعال باشد
 */

// جلوگیری از دسترسی مستقیم
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ثبت دسته‌بندی ویجت‌های سفارشی
 */
function crypto_sekhyab_add_elementor_widget_categories($elements_manager) {
    $elements_manager->add_category(
        'crypto-sekhyab',
        [
            'title' => __('کریپتو سخیاب', 'crypto-sekhyab'),
            'icon' => 'fa fa-bitcoin',
        ]
    );
}
add_action('elementor/elements/categories_registered', 'crypto_sekhyab_add_elementor_widget_categories');

/**
 * ویجت لیست ارزها
 */
class Crypto_Sekhyab_Crypto_List_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'crypto_list';
    }

    public function get_title() {
        return __('لیست ارزهای دیجیتال', 'crypto-sekhyab');
    }

    public function get_icon() {
        return 'eicon-table';
    }

    public function get_categories() {
        return ['crypto-sekhyab'];
    }

    protected function register_controls() {
        
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('تنظیمات', 'crypto-sekhyab'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'limit',
            [
                'label' => __('تعداد ارزها', 'crypto-sekhyab'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 100,
                'step' => 1,
                'default' => 10,
            ]
        );

        $this->end_controls_section();
        
        // استایل
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('استایل', 'crypto-sekhyab'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'table_heading_color',
            [
                'label' => __('رنگ عنوان‌های جدول', 'crypto-sekhyab'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .crypto-table th' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        echo do_shortcode('[crypto_list limit="' . esc_attr($settings['limit']) . '"]');
    }
}

/**
 * ویجت قیمت ارز
 */
class Crypto_Sekhyab_Crypto_Price_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'crypto_price';
    }

    public function get_title() {
        return __('قیمت ارز دیجیتال', 'crypto-sekhyab');
    }

    public function get_icon() {
        return 'eicon-price-table';
    }

    public function get_categories() {
        return ['crypto-sekhyab'];
    }

    protected function register_controls() {
        
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('تنظیمات', 'crypto-sekhyab'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'crypto_id',
            [
                'label' => __('شناسه CoinGecko', 'crypto-sekhyab'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'bitcoin',
                'placeholder' => 'bitcoin',
            ]
        );

        $this->add_control(
            'currency',
            [
                'label' => __('واحد پول', 'crypto-sekhyab'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'usd',
                'options' => [
                    'usd' => __('دلار', 'crypto-sekhyab'),
                    'irr' => __('تومان', 'crypto-sekhyab'),
                ],
            ]
        );

        $this->end_controls_section();
        
        // استایل
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('استایل', 'crypto-sekhyab'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => __('رنگ متن', 'crypto-sekhyab'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .crypto-price-inline' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'typography',
                'selector' => '{{WRAPPER}} .crypto-price-inline',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        echo do_shortcode('[crypto_price id="' . esc_attr($settings['crypto_id']) . '" currency="' . esc_attr($settings['currency']) . '"]');
    }
}

/**
 * ثبت ویجت‌ها
 */
function crypto_sekhyab_register_elementor_widgets($widgets_manager) {
    // ثبت ویجت لیست ارزها
    $widgets_manager->register(new Crypto_Sekhyab_Crypto_List_Widget());
    
    // ثبت ویجت قیمت ارز
    $widgets_manager->register(new Crypto_Sekhyab_Crypto_Price_Widget());
}
add_action('elementor/widgets/register', 'crypto_sekhyab_register_elementor_widgets');
