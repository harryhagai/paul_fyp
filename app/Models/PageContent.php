<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageContent extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'subtitle',
        'body',
        'settings',
        'is_published',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_published' => 'boolean',
    ];

    public static function defaults(string $slug): array
    {
        $defaults = [
            'about' => [
                'title' => 'Thoughtful products for little everyday joys.',
                'subtitle' => 'KidzStore365 helps parents find safe, cute, and practical essentials for children, from everyday wear to toys and gifts chosen with care.',
                'body' => 'Our store began with a simple idea: parents should not have to choose between adorable, practical, and safe.',
                'settings' => [
                    'story_title' => 'Built for families who want shopping to feel easier.',
                    'story_intro' => 'We focus on products that make childhood feel cared for: soft clothes, playful toys, thoughtful gifts, and essentials parents can trust.',
                    'story_body_1' => 'Our store began with a simple idea: parents should not have to choose between adorable, practical, and safe. Every collection is selected with real family routines in mind, so shopping feels calm and useful.',
                    'story_body_2' => 'Whether you are preparing for a newborn, refreshing school items, or choosing a gift, we keep the experience simple, friendly, and reliable.',
                    'promise_1_title' => 'Safety First',
                    'promise_1_text' => 'We prioritize products that feel dependable for children and reassuring for parents.',
                    'promise_2_title' => 'Chosen With Care',
                    'promise_2_text' => 'Items are selected for comfort, usefulness, charm, and everyday value.',
                    'promise_3_title' => 'Smooth Delivery',
                    'promise_3_text' => 'We work to keep ordering clear and delivery quick, secure, and predictable.',
                    'values_title' => 'What guides us',
                    'values_intro' => 'Simple values shape every product, every order, and every conversation with our customers.',
                    'band_title' => 'Ready to find something lovely?',
                    'band_text' => 'Browse thoughtful kids products chosen for comfort, joy, and everyday family life.',
                ],
            ],
            'contact' => [
                'title' => 'Contact Us',
                'subtitle' => "We'd love to hear from you! Get in touch with our team for any questions, support, or feedback.",
                'body' => null,
                'settings' => [
                    'form_title' => 'Send us a Message',
                    'info_title' => 'Get in Touch',
                    'email' => 'support@kidzstore365.com',
                    'phone' => '+255 123 456 789',
                    'address' => 'Dar es Salaam, Tanzania',
                    'hours_title' => 'Business Hours',
                    'weekday_label' => 'Monday - Friday',
                    'weekday_hours' => '9:00 AM - 6:00 PM',
                    'saturday_label' => 'Saturday',
                    'saturday_hours' => '10:00 AM - 4:00 PM',
                    'sunday_label' => 'Sunday',
                    'sunday_hours' => 'Closed',
                ],
            ],
        ];

        return $defaults[$slug] ?? [
            'title' => ucfirst(str_replace('-', ' ', $slug)),
            'subtitle' => null,
            'body' => null,
            'settings' => [],
        ];
    }

    public static function findPublishedOrDefault(string $slug): self
    {
        $page = static::where('slug', $slug)->where('is_published', true)->first();

        if ($page) {
            return $page;
        }

        return new static(array_merge(['slug' => $slug, 'is_published' => true], static::defaults($slug)));
    }

    public function mergedSettings(): array
    {
        $defaults = static::defaults($this->slug)['settings'] ?? [];

        return array_merge($defaults, $this->settings ?? []);
    }
}
