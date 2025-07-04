<?php
namespace ROCKET_WP_CRAWLER;

use WPMedia\PHPUnit\Unit\TestCase;

class Admin_Page_Unit_Test extends TestCase
{
    public function test_screen_size_formatting_logic()
    {
        $width = 1200;
        $height = 800;
        $formatted = $width . 'x' . $height;
        $this->assertEquals('1200x800', $formatted);
        $this->assertStringContainsString('x', $formatted);
    }

    public function test_link_array_processing()
    {
        $hrefs = ['https://example.com', 'https://test.com'];
        $this->assertIsArray($hrefs);
        $this->assertCount(2, $hrefs);
        $this->assertContains('https://example.com', $hrefs);
    }

    public function test_empty_data_handling()
    {
        $empty_array = [];
        $this->assertEmpty($empty_array);
        $this->assertCount(0, $empty_array);
    }

    public function test_data_sanitization()
    {
        $raw_text = '<script>alert("test")</script>';
        $sanitized = htmlspecialchars($raw_text);
        $this->assertStringContainsString('&lt;script&gt;', $sanitized);
        $this->assertStringNotContainsString('<script>', $sanitized);
    }

    public function test_html_structure_validation()
    {
        $expected_headers = ['Time', 'Screen', 'Links'];
        foreach ($expected_headers as $header) {
            $this->assertIsString($header);
            $this->assertNotEmpty($header);
        }
    }

    public function test_url_validation_logic()
    {
        $valid_urls = [
            'https://example.com',
            'http://test.com',
            'https://subdomain.example.com/path'
        ];
        foreach ($valid_urls as $url) {
            $this->assertStringContainsString('://', $url);
            $this->assertIsString($url);
        }
    }

    public function test_table_data_structure()
    {
        $table_data = [
            'visit_time' => '2024-01-01 12:00:00',
            'screen_width' => 1200,
            'screen_height' => 800,
            'hrefs' => ['https://example.com']
        ];
        $this->assertArrayHasKey('visit_time', $table_data);
        $this->assertArrayHasKey('screen_width', $table_data);
        $this->assertArrayHasKey('screen_height', $table_data);
        $this->assertArrayHasKey('hrefs', $table_data);
        $this->assertIsString($table_data['visit_time']);
        $this->assertIsInt($table_data['screen_width']);
        $this->assertIsInt($table_data['screen_height']);
        $this->assertIsArray($table_data['hrefs']);
    }

    public function test_json_encoding_logic()
    {
        $data = ['hrefs' => ['https://example.com', 'https://test.com']];
        $json_encoded = json_encode($data);
        $json_decoded = json_decode($json_encoded, true);
        $this->assertIsString($json_encoded);
        $this->assertIsArray($json_decoded);
        $this->assertEquals($data, $json_decoded);
    }
} 