export const getAdaptiveColor = (color: string | undefined | null, isDark: boolean): string => {
    if (!color) return isDark ? '#FFFFFF' : '#000000';

    let adaptiveColor = color;
    let hex = color;

    // Renk hex degilse (örn: "black", "blue") basit kontrol
    if (!color.startsWith('#')) {
        const lower = color.toLowerCase();
        if (isDark && (lower === 'black' || lower === '#000')) return '#FFFFFF';
        if (!isDark && (lower === 'white' || lower === '#fff')) return '#000000';
        return color; // Diger isimlendirilmis renkler oldugu gibi kalsin
    }

    // Hex temizle
    hex = hex.replace('#', '');
    if (hex.length === 3) hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];

    // RGB Parsingle
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);

    // Parlaklık (Luminance) Hesapla (0-255)
    // Formül: (R*299 + G*587 + B*114) / 1000
    const brightness = (r * 299 + g * 587 + b * 114) / 1000;

    if (isDark) {
        // DARK MODE: Siyah veya çok koyu renkleri Beyaz yap
        if (brightness < 40) adaptiveColor = '#FFFFFF';
    } else {
        // LIGHT MODE: 
        // 1. Beyaz veya aşırı parlakları Siyah yap
        if (brightness > 240) {
            adaptiveColor = '#000000';
        }
        // 2. "Cırtlak" renkleri (Sarı, Açık Mavi vs.) koyulaştır
        // Eşik değer: ~160
        else if (brightness > 160) {
            const darkenFactor = 0.65; // Rengi %35 koyulaştır
            const dr = Math.floor(r * darkenFactor).toString(16).padStart(2, '0');
            const dg = Math.floor(g * darkenFactor).toString(16).padStart(2, '0');
            const db = Math.floor(b * darkenFactor).toString(16).padStart(2, '0');
            adaptiveColor = `#${dr}${dg}${db}`;
        }
    }

    return adaptiveColor;
};
