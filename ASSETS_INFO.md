# Assets Bilgileri

## Mevcut Dosyalar
- ✅ `icon.png` - Ana uygulama ikonu (68 KB)
- ✅ `adaptive-icon.png` - Android adaptive icon (68 KB)
- ✅ `splash-icon.png` - Splash screen görseli (68 KB)
- ✅ `favicon.png` - Web favicon (68 KB)

## Önerilen Boyutlar

### icon.png
- **Boyut:** 1024x1024 px
- **Format:** PNG (şeffaf arka plan)
- **Kullanım:** iOS ve Android ana ikon

### adaptive-icon.png
- **Boyut:** 1024x1024 px
- **Format:** PNG (şeffaf arka plan)
- **Kullanım:** Android adaptive icon foreground
- **Not:** Ortadaki 512x512 px alan güvenli bölge

### splash-icon.png
- **Boyut:** Minimum 1242x2436 px (iPhone 11 Pro Max)
- **Format:** PNG
- **Kullanım:** Splash screen görseli
- **Not:** Görsel ortada, arka plan siyah (#000000)

### favicon.png
- **Boyut:** 48x48 px veya 192x192 px
- **Format:** PNG
- **Kullanım:** Web tarayıcı ikonu

## app.json Yapılandırması

Tüm asset referansları doğru şekilde ayarlandı:

```json
{
  "icon": "./assets/icon.png",
  "splash": {
    "image": "./assets/splash-icon.png",
    "resizeMode": "contain",
    "backgroundColor": "#000000"
  },
  "android": {
    "icon": "./assets/icon.png",
    "adaptiveIcon": {
      "foregroundImage": "./assets/adaptive-icon.png",
      "backgroundColor": "#000000"
    }
  },
  "ios": {
    "icon": "./assets/icon.png"
  },
  "web": {
    "favicon": "./assets/favicon.png"
  }
}
```

## Kontrol Listesi
- ✅ Tüm dosyalar mevcut
- ✅ app.json referansları doğru
- ✅ Android adaptive icon ayarlandı
- ✅ iOS icon ayarlandı
- ✅ Splash screen ayarlandı
- ✅ Web favicon ayarlandı
- ✅ Arka plan renkleri ayarlandı (#000000)

## Build Öncesi Kontrol
Uygulama build etmeden önce:
1. Icon dosyalarının 1024x1024 px olduğundan emin olun
2. Splash icon'un yeterli boyutta olduğunu kontrol edin
3. `npx expo prebuild --clean` komutu ile native klasörleri oluşturun
4. EAS Build ile test edin: `eas build --platform android --profile preview`
