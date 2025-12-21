import React, { useState, useEffect } from 'react';
import { View, Text, ScrollView, Linking, StatusBar, RefreshControl } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useTranslation } from 'react-i18next';
import { useTheme } from '../theme';
import { AppCard } from '../components/AppCard';
import { applicationsScreenStyles } from '../styles/applicationsScreenStyles';
import { ProfileService } from '../services/apiService';
import { SkeletonItem } from '../components/SkeletonItem';
import { LanguageSelector } from '../components/LanguageSelector';

interface AppItem {
    id: string;
    app_key: string;
    name_tr: string;
    name_en: string;
    desc_tr: string;
    desc_en: string;
    icon: string;
    color: string;
    url: string;
}

export const ApplicationsScreen: React.FC = () => {
    const { isDark, colors } = useTheme();
    const { t, i18n } = useTranslation();
    const [apps, setApps] = useState<AppItem[]>([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    useEffect(() => {
        fetchApps();
    }, []);

    const fetchApps = async () => {
        const data = await ProfileService.getApps();
        if (data) {
            setApps(data);
        }
        setLoading(false);
    };

    const onRefresh = async () => {
        setRefreshing(true);
        await fetchApps();
        setRefreshing(false);
    };

    const handleAppPress = (url: string) => {
        Linking.openURL(url);
    };

    return (
        <SafeAreaView style={[applicationsScreenStyles.container, { backgroundColor: colors.background }]} edges={['top', 'left', 'right']}>
            <StatusBar
                barStyle={isDark ? 'light-content' : 'dark-content'}
                backgroundColor={colors.background}
            />

            <ScrollView
                showsVerticalScrollIndicator={false}
                contentContainerStyle={applicationsScreenStyles.scrollContent}
                refreshControl={
                    <RefreshControl
                        refreshing={refreshing}
                        onRefresh={onRefresh}
                        tintColor={colors.accent}
                        colors={[colors.accent]}
                        progressBackgroundColor={isDark ? colors.card : colors.background}
                    />
                }
            >
                <LanguageSelector />
                <View style={applicationsScreenStyles.headerContainer}>
                    <Text style={[applicationsScreenStyles.title, { color: colors.text }]}>
                        {t('apps.title')}
                    </Text>
                </View>

                {loading ? (
                    <View style={applicationsScreenStyles.appsContainer}>
                        <SkeletonItem height={90} />
                        <SkeletonItem height={90} />
                    </View>
                ) : (
                    <View style={applicationsScreenStyles.appsContainer}>
                        {apps.map((app, index) => (
                            <AppCard
                                key={app.id}
                                app={{
                                    id: app.app_key,
                                    name: i18n.language === 'tr' ? app.name_tr : app.name_en,
                                    description: i18n.language === 'tr' ? app.desc_tr : app.desc_en,
                                    icon: app.icon,
                                    color: app.color,
                                }}
                                index={index}
                                onPress={() => handleAppPress(app.url)}
                            />
                        ))}
                    </View>
                )}
            </ScrollView>
        </SafeAreaView>
    );
};
