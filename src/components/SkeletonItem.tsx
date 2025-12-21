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
    const { isDark, colors } = useTheme();
    const opacity = useRef(new Animated.Value(0.3)).current;

    useEffect(() => {
        const animation = Animated.loop(
            Animated.sequence([
                Animated.timing(opacity, {
                    toValue: 0.7,
                    duration: 800,
                    useNativeDriver: true,
                }),
                Animated.timing(opacity, {
                    toValue: 0.3,
                    duration: 800,
                    useNativeDriver: true,
                }),
            ])
        );
        animation.start();

        return () => animation.stop();
    }, []);

    const baseColor = isDark ? '#2A2A2A' : '#E0E0E0';

    return (
        <Animated.View
            style={{
                width,
                height,
                borderRadius,
                marginBottom,
                backgroundColor: baseColor,
                opacity: opacity,
                elevation: 0, // Ensure no shadow on Android
                borderWidth: 0,
            }}
        />
    );
};
