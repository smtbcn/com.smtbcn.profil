import React, { useState, useEffect, useCallback } from 'react';
import { ScrollView, View, Text, Image, StyleSheet, Linking, RefreshControl } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useTranslation } from 'react-i18next';
import { useTheme } from '../theme';
import { AboutInfo } from '../components/AboutInfo';
import { SocialButton } from '../components/SocialButton';
import { socialLinks, getPath } from '../constants/socialLinks';
import { ProfileService } from '../services/apiService';
import { FontAwesome5 } from '@expo/vector-icons';
import { LanguageSelector } from '../components/LanguageSelector';
import { getAdaptiveColor } from '../utils/colorUtils';

export const AboutScreen: React.FC = () => {
    const { colors, isDark } = useTheme();
    const { i18n, t } = useTranslation();
    const [skills, setSkills] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    const fetchData = useCallback(async () => {
        const data = await ProfileService.getProfileData();
        if (data && data.skills) {
            setSkills(data.skills);
        }
        setLoading(false);
    }, []);

    const onRefresh = useCallback(async () => {
        setRefreshing(true);
        await fetchData();
        setRefreshing(false);
    }, [fetchData]);

    useEffect(() => {
        fetchData();
    }, [fetchData]);

    const handleSocialPress = (url: string) => {
        Linking.openURL(url);
    };

    return (
        <SafeAreaView style={{ flex: 1, backgroundColor: colors.background }} edges={['top', 'left', 'right']}>
            <ScrollView
                showsVerticalScrollIndicator={false}
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
                <View style={styles.header}>
                    <Image
                        source={{ uri: 'https://avatars.githubusercontent.com/u/75270742?v=4' }}
                        style={[styles.avatar, { borderColor: colors.border }]}
                    />
                    <Text style={[styles.name, { color: colors.text }]}>Samet BİÇEN</Text>
                    <Text style={[styles.title, { color: colors.textSecondary }]}>Full Stack Mobile Developer</Text>
                </View>

                <View style={styles.section}>
                    <Text style={[styles.sectionTitle, { color: colors.text }]}>{t('profile.aboutMe')}</Text>
                    <AboutInfo />
                </View>

                <View style={styles.section}>
                    <Text style={[styles.sectionTitle, { color: colors.text, paddingHorizontal: 20 }]}>{t('profile.skills')}</Text>
                    <View style={styles.skillsGrid}>
                        {skills.map((skill: any) => {
                            // Akıllı Renk Adaptasyonu
                            const adaptiveColor = getAdaptiveColor(skill.color, isDark);

                            return (
                                <View key={skill.name} style={[styles.skillBadge, { backgroundColor: colors.card, borderColor: colors.border }]}>
                                    {skill.icon && <FontAwesome5 name={skill.icon} size={24} color={adaptiveColor} />}
                                    <Text style={[styles.skillText, { color: adaptiveColor }]} numberOfLines={1}>{skill.name}</Text>
                                </View>
                            );
                        })}
                    </View>
                </View>

                <View style={[styles.section, { marginTop: 5, paddingBottom: 40 }]}>
                    <Text style={[styles.sectionTitle, { color: colors.text, paddingHorizontal: 20 }]}>{t('profile.contact')}</Text>
                    <View style={styles.buttonsContainer}>
                        {socialLinks.map((item, index) => (
                            <SocialButton
                                key={item.label}
                                label={item.label}
                                icon={item.icon}
                                color={item.color}
                                path={getPath(item.url)}
                                onPress={() => handleSocialPress(item.url)}
                                index={index}
                            />
                        ))}
                    </View>
                </View>
            </ScrollView>
        </SafeAreaView>
    );
};

const styles = StyleSheet.create({
    header: {
        alignItems: 'center',
        paddingVertical: 30,
    },
    avatar: {
        width: 100,
        height: 100,
        borderRadius: 50,
        borderWidth: 2,
    },
    name: {
        fontSize: 24,
        fontWeight: 'bold',
        marginTop: 10,
    },
    title: {
        fontSize: 16,
        opacity: 0.7,
    },
    section: {
        marginTop: 20,
    },
    sectionTitle: {
        fontSize: 20,
        fontWeight: 'bold',
        marginBottom: 10,
        paddingHorizontal: 20,
    },
    skillsGrid: {
        flexDirection: 'row',
        flexWrap: 'wrap',
        paddingHorizontal: 20,
        justifyContent: 'space-between',
        gap: 12,
    },
    skillBadge: {
        width: '30%',
        aspectRatio: 1,
        padding: 10,
        borderRadius: 16,
        borderWidth: 1,
        justifyContent: 'center',
        alignItems: 'center',
    },
    skillText: {
        fontSize: 11,
        fontWeight: 'bold',
        marginTop: 6,
        textAlign: 'center',
    },
    buttonsContainer: {
        paddingHorizontal: 20,
        gap: 8,
    }
});
