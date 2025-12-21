import React, { useRef, useEffect } from 'react';
import { Animated, DimensionValue, Platform } from 'react-native';
import { useTheme } from '../theme';

interface SkeletonItemProps {
    width?: DimensionValue;
    height?: DimensionValue;
    borderRadius?: number;
    marginBottom?: number;
}

export const SkeletonItem: React.FC<SkeletonItemProps> = ({
    width = '100%',
    height = 100,
    borderRadius = 12,
    marginBottom = 12
}) => {
    const { isDark } = useTheme();
    // Android'de opacity yerine renk interpolasyonu kullanarak "dark shadow/artifact" sorununu çözüyoruz.
    // Opacity kullanımı bazen Android'de arka planın (siyah) sızmasına neden olabilir.
    const animValue = useRef(new Animated.Value(0)).current;

    useEffect(() => {
        const animation = Animated.loop(
            Animated.sequence([
                Animated.timing(animValue, {
                    toValue: 1,
                    duration: 1000,
                    useNativeDriver: false, // backgroundColor animasyonu için false olmalı
                }),
                Animated.timing(animValue, {
                    toValue: 0,
                    duration: 1000,
                    useNativeDriver: false,
                }),
            ])
        );
        animation.start();

        return () => animation.stop();
    }, []);

    // Renkleri tanımla: Opacity kullanmadan iki gri ton arasında geçiş yap
    const lightColor = isDark ? '#2A2A2A' : '#F0F0F0';
    const darkColor = isDark ? '#383838' : '#E0E0E0';

    const backgroundColor = animValue.interpolate({
        inputRange: [0, 1],
        outputRange: [lightColor, darkColor]
    });

    return (
        <Animated.View
            style={{
                width,
                height,
                borderRadius,
                marginBottom,
                backgroundColor: backgroundColor,
                // Opacity tamamen kaldırıldı
                // Android artifact önlemleri
                elevation: 0,
                overflow: 'hidden',
                shadowColor: 'transparent',
                shadowOpacity: 0,
                shadowRadius: 0,
                shadowOffset: { width: 0, height: 0 },
                borderWidth: 0,
            }}
        />
    );
};
