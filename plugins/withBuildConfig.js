const { withAppBuildGradle, withMainActivity, withMainApplication } = require('@expo/config-plugins');

module.exports = function withBuildConfig(config) {
    // 1. build.gradle içinde buildConfig true yap
    config = withAppBuildGradle(config, (config) => {
        if (config.modResults.language === 'groovy') {
            config.modResults.contents = enableBuildConfig(config.modResults.contents);
        }
        return config;
    });

    // 2. MainActivity.kt ve MainApplication.kt içine import ekle
    config = withAndroidImports(config);

    return config;
};

// Kotlin dosyalarına BuildConfig importu ekle
function withAndroidImports(config) {
    const packageName = config.android?.package || 'com.smtbcn.profil';
    const importStatement = `import ${packageName}.BuildConfig`;

    config = withMainActivity(config, (config) => {
        if (config.modResults.language === 'kotlin') {
            if (!config.modResults.contents.includes(importStatement)) {
                // Package tanımlamasından sonra importu ekle
                config.modResults.contents = config.modResults.contents.replace(
                    /package (.*)\n/,
                    `package $1\n\n${importStatement}\n`
                );
            }
        }
        return config;
    });

    config = withMainApplication(config, (config) => {
        if (config.modResults.language === 'kotlin') {
            if (!config.modResults.contents.includes(importStatement)) {
                config.modResults.contents = config.modResults.contents.replace(
                    /package (.*)\n/,
                    `package $1\n\n${importStatement}\n`
                );
            }
        }
        return config;
    });

    return config;
}

function enableBuildConfig(contents) {
    if (contents.includes('buildConfig true') || contents.includes('buildConfig = true')) {
        return contents;
    }

    // Eğer buildFeatures bloğu varsa içine ekle
    if (contents.includes('buildFeatures')) {
        return contents.replace(
            /buildFeatures\s*{/,
            'buildFeatures {\n        buildConfig true\n'
        );
    }

    // Yoksa android bloğunun hemen içine ekle
    return contents.replace(
        /android\s*{/,
        'android {\n    buildFeatures {\n        buildConfig true\n    }\n'
    );
}
