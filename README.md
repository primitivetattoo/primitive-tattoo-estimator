# Primitive Tattoo Price Estimator

A WordPress plugin that gives tattoo studio visitors an instant price estimate through an interactive 4-step wizard. Built for [Primitive Tattoo Bali](https://primitivetattoo.com).

**[Live Demo — Tattoo Price Estimator](https://primitivetattoobali.com/tattoo-price-estimator-bali/)**

![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue?logo=wordpress)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple?logo=php)
![License](https://img.shields.io/badge/License-GPL--2.0--or--later-green)
![Version](https://img.shields.io/badge/Version-1.2.6-gold)

## How It Works

Visitors walk through 4 steps to get a price range:

1. **Style** — Fine Line, Blackwork, Mandala, Traditional, Realism, Watercolor
2. **Size** — Small (coin) to Extra Large (full panel)
3. **Placement** — Wrist, Forearm, Ribcage, Neck, etc.
4. **Complexity** — Simple, Moderate, or Detailed

Each option has a configurable multiplier that adjusts the final price range. The result page shows the estimated range and a WhatsApp booking button with a pre-filled message.

## Features

- **4-step pricing wizard** with smooth transitions
- **Configurable multipliers** — adjust pricing per style, size, placement, and complexity from the admin
- **Promo banner** — toggleable promotional offer with custom title, price, and CTA
- **WhatsApp integration** — pre-filled booking messages with estimate details
- **Elementor widget** — drag-and-drop with per-instance font overrides
- **Custom typography** — set heading and body fonts (auto-loads Google Fonts)
- **Shortcode support** — `[ptb_estimator]` works on any page
- **Mobile responsive** — works on all screen sizes
- **IDR currency formatting** — built for Indonesian Rupiah

## Installation

1. Download the latest release zip
2. Go to **Plugins > Add New > Upload Plugin** in WordPress admin
3. Upload the zip and activate
4. Configure pricing at **Settings > Tattoo Estimator**
5. Add `[ptb_estimator]` to any page or use the Elementor widget

## Configuration

All settings are managed from **Settings > Tattoo Estimator**:

| Section | What You Can Configure |
|---------|----------------------|
| General | WhatsApp number for booking |
| Typography | Heading and body font (Google Fonts or system) |
| Promo Banner | Toggle, title, subtitle, price, CTA URL, WhatsApp message |
| Size Pricing | Base min/max price ranges per size tier |
| Style Multipliers | Price multiplier per tattoo style |
| Placement Multipliers | Price multiplier per body area |
| Complexity Multipliers | Price multiplier per detail level |

### Pricing Formula

```
Final Price Range = Base Size Range × Style Multiplier × Placement Multiplier × Complexity Multiplier
```

**Example:** Medium Fine Line on Ribcage, Detailed complexity:
- Base: $100 – $250
- × Fine Line (1.2) × Ribcage (1.2) × Detailed (1.65)
- = **$237 – $594**

## Usage

### Shortcode
```
[ptb_estimator]
```

### Elementor
Search for "Tattoo Price Estimator" in the Elementor widget panel. The widget supports per-instance font overrides for heading and body text.

## Screenshots

The estimator renders as a dark-themed card with step indicators, option cards for each selection, and a results page with the price range and WhatsApp booking button.

## Requirements

- WordPress 5.8+
- PHP 7.4+
- Elementor (optional, for widget support)

## License

GPL-2.0-or-later. See [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html).

---

Built by [Primitive Tattoo Bali](https://primitivetattoo.com)
