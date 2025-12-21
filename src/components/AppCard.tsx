import React, { useRef, useEffect } from 'react';
import { View, Text, Pressable, Animated, Image, StyleSheet } from 'react-native';
import { FontAwesome, FontAwesome5 } from '@expo/vector-icons';
import { useTheme } from '../theme';
import { applicationsScreenStyles } from '../styles/applicationsScreenStyles';
import { getAdaptiveColor } from '../utils/colorUtils';

interface AppItem {
    id: string; // This is actually the platform key (android/apple)
    name: string;
    description: string;
    icon: string;
    color: string;
}

interface AppCardProps {
    app: AppItem;
    onPress: () => void;
    index: number;
}

export const AppCard: React.FC<AppCardProps> = ({ app, onPress, index }) => {
    const { colors, isDark } = useTheme();
    const fadeAnim = useRef(new Animated.Value(0)).current;
    const slideAnim = useRef(new Animated.Value(20)).current;

    useEffect(() => {
        Animated.parallel([
            Animated.timing(fadeAnim, {
                toValue: 1,
                duration: 500,
                delay: index * 150,
                useNativeDriver: true,
            }),
            Animated.timing(slideAnim, {
                toValue: 0,
                duration: 500,
                delay: index * 150,
                useNativeDriver: true,
            }),
        ]).start();
    }, [index]);

    const isUrl = app.icon?.startsWith('http');
    const isAndroid = app.id === 'android';
    const adaptiveColor = getAdaptiveColor(app.color, isDark);

    return (
        <Animated.View
            style={{
                opacity: fadeAnim,
                transform: [{ translateY: slideAnim }],
            }}
        >
            <Pressable onPress={onPress}>
                {({ pressed }) => (
                    <View
                        style={[
                            applicationsScreenStyles.appCard,
                            {
                                backgroundColor: colors.card,
                                borderColor: colors.border,
                                elevation: 3,
                                shadowColor: '#000',
                                shadowOffset: { width: 0, height: 2 },
                                shadowOpacity: 0.1,
                                shadowRadius: 4,
                            },
                            pressed && applicationsScreenStyles.appCardPressed,
                        ]}
                    >
                        <View style={styles.iconWrapper}>
                            <View style={styles.mainIconContainer}>
                                {isUrl ? (
                                    <Image
                                        source={{ uri: app.icon }}
                                        style={styles.largeIcon}
                                        resizeMode="cover"
                                    />
                                ) : (
                                    <View style={[styles.largeIcon, { justifyContent: 'center', alignItems: 'center', backgroundColor: colors.background }]}>
                                        <FontAwesome name={app.icon as any || 'mobile'} size={45} color={adaptiveColor} />
                                    </View>
                                )}
                            </View>

                            {/* Platform Badge (Overlay) */}
                            <View style={[
                                styles.platformBadge,
                                { backgroundColor: isAndroid ? '#3DDC84' : '#FFFFFF' }
                            ]}>
                                {isAndroid ? (
                                    <FontAwesome5 name="android" size={15} color="#000" />
                                ) : (
                                    <FontAwesome5 name="apple" size={18} color="#000" />
                                )}
                            </View>
                        </View>

                        <View style={applicationsScreenStyles.appInfo}>
                            <Text style={[applicationsScreenStyles.appName, { color: colors.text }]}>
                                {app.name}
                            </Text>
                            <Text
                                style={[applicationsScreenStyles.appDescription, { color: colors.textSecondary }]}
                                numberOfLines={2}
                            >
                                {app.description}
                            </Text>
                        </View>

                        <FontAwesome name="chevron-right" size={14} color={colors.textSecondary as any} />
                    </View>
                )}
            </Pressable>
        </Animated.View>
    );
};

const styles = StyleSheet.create({
    iconWrapper: {
        position: 'relative',
        marginRight: 16,
    },
    mainIconContainer: {
        width: 75,
        height: 75,
        borderRadius: 16,
        overflow: 'hidden',
        borderWidth: 1,
        borderColor: 'rgba(255,255,255,0.1)',
    },
    largeIcon: {
        width: '100%',
        height: '100%',
    },
    platformBadge: {
        position: 'absolute',
        top: -6,
        right: -6,
        width: 28,
        height: 28,
        borderRadius: 14,
        justifyContent: 'center',
        alignItems: 'center',
        borderWidth: 2.5,
        borderColor: '#FFFFFF',
        zIndex: 10,
        elevation: 4,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.3,
        shadowRadius: 2,
    }

});
