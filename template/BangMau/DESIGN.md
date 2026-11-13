---
name: Haute Gastronomie & Culinary Reserve
colors:
  surface: '#fff8f5'
  surface-dim: '#e9d7c8'
  surface-bright: '#fff8f5'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#fff1e8'
  surface-container: '#feeadc'
  surface-container-high: '#f8e5d6'
  surface-container-highest: '#f2dfd1'
  on-surface: '#231a11'
  on-surface-variant: '#5b403c'
  inverse-surface: '#392e25'
  inverse-on-surface: '#ffeee1'
  outline: '#906f6a'
  outline-variant: '#e4beb8'
  surface-tint: '#b91d12'
  primary: '#870000'
  on-primary: '#ffffff'
  primary-container: '#af140b'
  on-primary-container: '#ffbfb4'
  inverse-primary: '#ffb4a8'
  secondary: '#4a6633'
  on-secondary: '#ffffff'
  secondary-container: '#c8eaa9'
  on-secondary-container: '#4e6b36'
  tertiary: '#633500'
  on-tertiary: '#ffffff'
  tertiary-container: '#844800'
  on-tertiary-container: '#ffc18e'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#ffdad4'
  primary-fixed-dim: '#ffb4a8'
  on-primary-fixed: '#410000'
  on-primary-fixed-variant: '#930000'
  secondary-fixed: '#cbedac'
  secondary-fixed-dim: '#afd192'
  on-secondary-fixed: '#0c2000'
  on-secondary-fixed-variant: '#334e1d'
  tertiary-fixed: '#ffdcc1'
  tertiary-fixed-dim: '#ffb779'
  on-tertiary-fixed: '#2e1500'
  on-tertiary-fixed-variant: '#6c3a00'
  background: '#fff8f5'
  on-background: '#231a11'
  surface-variant: '#f2dfd1'
typography:
  display-lg:
    fontFamily: Playfair Display
    fontSize: 56px
    fontWeight: '600'
    lineHeight: 64px
    letterSpacing: -0.02em
  display-lg-mobile:
    fontFamily: Playfair Display
    fontSize: 38px
    fontWeight: '600'
    lineHeight: 46px
    letterSpacing: -0.01em
  headline-lg:
    fontFamily: Playfair Display
    fontSize: 40px
    fontWeight: '600'
    lineHeight: 48px
    letterSpacing: -0.015em
  headline-lg-mobile:
    fontFamily: Playfair Display
    fontSize: 28px
    fontWeight: '600'
    lineHeight: 36px
    letterSpacing: 0em
  headline-md:
    fontFamily: Playfair Display
    fontSize: 32px
    fontWeight: '500'
    lineHeight: 40px
  headline-md-mobile:
    fontFamily: Playfair Display
    fontSize: 24px
    fontWeight: '500'
    lineHeight: 32px
  headline-sm:
    fontFamily: Playfair Display
    fontSize: 22px
    fontWeight: '500'
    lineHeight: 28px
  body-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 15px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 20px
  label-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
    letterSpacing: 0.02em
  label-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.04em
  label-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 11px
    fontWeight: '700'
    lineHeight: 14px
    letterSpacing: 0.06em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  gutter: 1.5rem
  gutter-mobile: 1rem
  margin: 3rem
  margin-mobile: 1.25rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2.5rem
  space-xxl: 4rem
---

## Brand & Style

### Brand Personality & Philosophy
This design system embodies high-end epicurean hospitality: intimate, authoritative, sensory, and meticulously crafted. It captures the atmosphere of Michelin-caliber dining—the crisp rustle of linen, polished silver, warm candlelight, and artisanal passion. The experience avoids sterile digital interactions, aiming instead to evoke warmth, prestige, and quiet luxury.

### Target Audience & Emotional Impact
- **Audience:** Discerning diners, gourmets, VIP guests, culinary travelers, and event organizers seeking elevated dining, curated chef tables, and seamless reservations.
- **Emotional Response:** Comfort and anticipation, heightened appetite, trust in culinary excellence, and an unmistakable sense of exclusive privilege.

### Visual Style Direction
The aesthetic merges **Modern Luxury Editorial** with **Warm Tactile Minimalism**:
- **Canvas Base:** Soft ivory and warm linen undertones replace stark white, creating a tranquil, low-strain sensory environment.
- **Micro-textures & Lighting:** Warm ambient drop shadows and subtle gold hair-lines replicate ambient restaurant lighting and gilded menu folios.
- **Typographic Tension:** High-contrast editorial serifs deliver literary grandeur in headlines, balanced against modern, ergonomic sans-serifs for operational clarity in reservation flows.

## Colors

### Palette System & Functional Roles
- **Primary Brand (`#AF140B` - Ruby Wine):** The focal anchor. Reserved strictly for primary confirmation CTAs, brand identity, premium pricing emphasis, selected calendar dates, and primary booking triggers.
- **Secondary / Natural (`#3C5826` - Forest Olive):** Signifies organic produce, farm-to-table notes, confirmed bookings, vacant/available table slots, and verified dietary badges.
- **Warning / Heat Accent (`#FD8F00` - Ember Amber):** Evokes culinary fire and artisanal preparation. Used for signature dish flags, urgent availability alerts ("Only 2 tables left"), and interactive notices.
- **Subtle Gold (`#FDD364` - Honey Gold):** Used for Michelin stars, guest ratings, VIP/Sommelier cellar privileges, and delicate divider strokes.
- **Neutral Canvas (`#FDEDD7` - Ivory Linen):** The universal background tone. Eliminates blue-light glare and establishes an ambient, paper-like menu texture.
- **Neutral Deep (`#2B2118` - Espresso Noir):** Deep roasted umber for maximum typographic legibility, avoiding harsh generic blacks (`#000000`).

### Application Hierarchy & Contrast Rules
- Canvas surfaces use `#FDEDD7`; elevated cards and dialog containers use `#FFF9F0` (Porcelain Warmth) to layer contrast against the canvas.
- Primary CTA buttons (`#AF140B`) must pair strictly with `#FFFFFF` or `#FDEDD7` text to exceed WCAG AA 4.5:1 legibility standards.
- Secondary badges (`#3C5826`) utilize soft tinted backgrounds (`rgba(60, 88, 38, 0.08)`) with `#3C5826` text, or solid fills with `#FFFFFF`.
- Gold accents (`#FDD364`) are paired with `#2B2118` text for chips and badges to ensure high visibility.

## Typography

### Font Pairing Strategy
- **Display & Headlines (Playfair Display):** Conveys bespoke hospitality, culinary prestige, and classic menu calligraphy. Used for culinary titles, tasting menu categories, and narrative storytelling.
- **Body & Data Displays (Plus Jakarta Sans):** Selected for its modern warmth, geometric balance, and legibility across reservation forms, table floor maps, ingredients lists, and operational notices.

### Typographic Hierarchy & Scale Management
- Maintain an editorial hierarchy: Headline levels introduce sensory descriptions, followed by balanced body sizes that keep meal courses easily readable.
- Prices, time slots, and party sizes use `label-lg` or `body-md` set in semi-bold weights with tabular numbers to prevent horizontal layout shift during reservation steps.

## Layout & Spacing

### Layout Model
- **Grid Architecture:** 12-column responsive layout on desktop (max width: `1280px` centered), collapsing to 6 columns on tablet (`768px - 1024px`) and 4 columns on mobile (`< 768px`).
- **Rhythm & Breathing Room:** Generous vertical sections (`space-xxl`) establish an unhurried, luxury pace, preventing booking screens from feeling transactional.
- **Reservation Layouts:** A two-tier layout format is standard: left rail (40% width) for contextual storytelling, venue imagery, and cancellation policies; right rail (60% width) dedicated to date, time slot, party size, and table selection. On mobile, this stacks into a single linear flow with a persistent bottom reservation bar.

## Elevation & Depth

### Atmospheric Warm Lighting Depth
Visual layering relies on warm tinted shadows rather than sterile grey drop shadows. Shadows carry a subtle amber/espresso tint (`rgba(43, 33, 24, ...)`) to harmonize with `#FDEDD7`.

### Elevation Tiers
1. **Level 0 (Base Canvas):** Background canvas (`#FDEDD7`) without shadows.
2. **Level 1 (Surface Cards & Course Modules):** Background `#FFF9F0` with subtle border `1px solid rgba(175, 20, 11, 0.08)` and shadow `0 2px 8px -2px rgba(43, 33, 24, 0.04), 0 4px 16px -4px rgba(43, 33, 24, 0.06)`.
3. **Level 2 (Floating Pickers, Dropdowns & Interactive Cards):** Shadow `0 8px 24px -6px rgba(43, 33, 24, 0.08), 0 16px 32px -8px rgba(43, 33, 24, 0.06)`.
4. **Level 3 (Modal Sheets, Table Allocation Overlays & VIP Drawers):** Shadow `0 20px 48px -10px rgba(43, 33, 24, 0.16), 0 32px 64px -16px rgba(43, 33, 24, 0.12)`.

## Shapes

### Corner Radii Guidelines
- **Small Elements (Input fields, time chips, buttons):** `12px` (`0.75rem`) for a tailored, welcoming touch that avoids clinical sharpness.
- **Card Containers (Menu items, booking cards, table items):** `16px` (`1rem`) to complement dish photography and structured sections.
- **Floating Overlays & Modal Drawers:** `24px` (`1.5rem`) on top-level sheets and desktop dialogue frames.
- **Avatars & Status Indicators:** Full circular (`rounded-full`) for guest profile avatars and table status indicators.

## Components

### Buttons
- **Primary CTA ("Reserve Table", "Confirm Booking"):** Background `#AF140B`, text `#FFFFFF`, border-radius `12px`, padding `14px 28px`. Hover state: subtle brightness reduction with warm elevation shadow `0 6px 20px rgba(175, 20, 11, 0.28)`.
- **Secondary / Terroir Action ("Browse Cellar", "View Menu"):** Background `transparent`, border `1.5px solid #3C5826`, text `#3C5826`. Hover state: fills with `rgba(60, 88, 38, 0.06)`.
- **Tertiary / Utility:** Text-only with an underline accent using `#FDD364` or `#AF140B`.

### Time & Availability Chips
- **Available Time Slot:** Background `#FFF9F0`, border `1px solid rgba(60, 88, 38, 0.3)`, text `#3C5826`, radius `10px`.
- **Selected Time Slot:** Background `#AF140B`, border `1px solid #AF140B`, text `#FFFFFF`, radius `10px`.
- **Sold Out / Unavailable:** Background `rgba(43, 33, 24, 0.04)`, border `1px solid rgba(43, 33, 24, 0.1)`, text `rgba(43, 33, 24, 0.35)`, strike-through label.

### Culinary Cards (Dish & Course Cards)
- Container background `#FFF9F0` with `16px` radius and Level 1 elevation.
- 16:10 ratio imagery with subtle inner vignettes.
- Signature dishes feature an Ember Amber (`#FD8F00`) pill badge at top-right with label "Chef's Signature".
- Dietary tags (Organic, Vegan, Halal) rendered in Forest Olive (`#3C5826`) micro-chips at bottom-left.

### Form Inputs & Date Pickers
- **Text Inputs:** Height `52px`, background `#FFF9F0`, border `1.5px solid rgba(43, 33, 24, 0.15)`, radius `12px`, padding `0 18px`.
- **Focus State:** Border transitions to `#AF140B` with an ambient glow of `0 0 0 3px rgba(175, 20, 11, 0.12)`.
- **Date Pickers:** Selected day active state in `#AF140B` with white text; days with limited inventory highlighted by a subtle `#FD8F00` dot below the numeral.

### Checkboxes & Radio Buttons
- **Checkboxes:** Size `20px x 20px`, `6px` radius. Active state fills with `#3C5826` and renders a crisp `#FFFFFF` checkmark.
- **Radio Buttons (Seating preferences: Terrace, Main Hall, Chef Counter):** Circular with a `#AF140B` inner dot on an ivory base.

### Specialized Hospitality Components
- **VIP & Sommelier Badge:** Gold pill (`#FDD364`) with `#2B2118` typography and a micro star icon.
- **Interactive Floor Map Table Indicator:** Available tables illuminated in `#3C5826`, reserved tables in muted taupe, and user's selected table highlighted in `#AF140B` with an animated pulse ring.