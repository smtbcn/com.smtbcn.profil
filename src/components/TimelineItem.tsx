import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Linking } from 'react-native';
import { FontAwesome5 } from '@expo/vector-icons';
import { useTheme } from '../theme';
import { useTranslation } from 'react-i18next';
import { getAdaptiveColor } from '../utils/colorUtils';

interface TimelineItemProps {
    item: {
        title_tr: string;
        title_en: string;
        desc_tr: string;
        desc_en: string;
        event_date: string;
        icon: string;
        color: string;
        link?: string;
    };
    isLast: boolean;
}

export const TimelineItem: React.FC<TimelineItemProps> = ({ item, isLast }) => {
    const { colors, isDark } = useTheme();
    const { i18n } = useTranslation();

    const title = i18n.language === 'tr' ? item.title_tr : item.title_en;
    const desc = i18n.language === 'tr' ? item.desc_tr : item.desc_en;

    // Akıllı Renk
    const adaptiveColor = getAdaptiveColor(item.color || colors.primary, isDark);

    const handlePress = () => {
        if (item.link) {
            Linking.openURL(item.link);
        }
    };

    return (
        <View style={styles.container}>
            {/* Left Timeline Line */}
            <View style={styles.lineSection}>
                <View style={[styles.dot, { backgroundColor: adaptiveColor }]} />
                {!isLast && <View style={[styles.line, { backgroundColor: colors.border }]} />}
            </View>

            {/* Right Content */}
            <TouchableOpacity
                style={[styles.contentCard, { backgroundColor: colors.card, borderColor: colors.border }]}
                onPress={handlePress}
                activeOpacity={item.link ? 0.7 : 1}
            >
                <View style={styles.header}>
                    <View style={[styles.iconContainer, { backgroundColor: adaptiveColor + '22' }]}>
                        <FontAwesome5 name={item.icon || 'rocket'} size={16} color={adaptiveColor} />
                    </View>
                    <View style={{ flex: 1 }}>
                        <Text style={[styles.date, { color: colors.textSecondary }]}>{item.event_date}</Text>
                        <Text style={[styles.title, { color: colors.text }]}>{title}</Text>
                    </View>
                </View>
                {desc ? (
                    <Text style={[styles.desc, { color: colors.textSecondary }]} numberOfLines={3}>
                        {desc}
                    </Text>
                ) : null}
            </TouchableOpacity>
        </View>
    );
};

const styles = StyleSheet.create({
    container: {
        flexDirection: 'row',
        minHeight: 100,
    },
    lineSection: {
        width: 30,
        alignItems: 'center',
    },
    dot: {
        width: 12,
        height: 12,
        borderRadius: 6,
        zIndex: 2,
        marginTop: 15,
    },
    line: {
        width: 2,
        flex: 1,
        position: 'absolute',
        top: 25,
        bottom: 0,
    },
    contentCard: {
        flex: 1,
        marginLeft: 10,
        marginBottom: 20,
        borderRadius: 16,
        padding: 16,
        borderWidth: 1,
    },
    header: {
        flexDirection: 'row',
        alignItems: 'center',
        marginBottom: 10,
    },
    iconContainer: {
        width: 36,
        height: 36,
        borderRadius: 18,
        justifyContent: 'center',
        alignItems: 'center',
        marginRight: 12,
    },
    title: {
        fontSize: 16,
        fontWeight: 'bold',
    },
    date: {
        fontSize: 12,
        fontWeight: '600',
        marginBottom: 2,
    },
    desc: {
        fontSize: 14,
        lineHeight: 20,
    }
});
