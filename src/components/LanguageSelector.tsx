import React from 'react';
import { View, Text, Pressable, StyleSheet } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { useTranslation } from 'react-i18next';
import { Ionicons } from '@expo/vector-icons';
import { useTheme } from '../theme';

const LANGUAGE_KEY = 'user-language';

export const LanguageSelector: React.FC = () => {
    const { colors } = useTheme();
    const { i18n } = useTranslation();

    const toggleLanguage = async () => {
        const nextLang = i18n.language === 'tr' ? 'en' : 'tr';
        await i18n.changeLanguage(nextLang);
        await AsyncStorage.setItem(LANGUAGE_KEY, nextLang);
    };

    return (
        <View style={styles.container}>
            <Pressable
                onPress={toggleLanguage}
                style={({ pressed }) => [
                    styles.button,
                    {
                        backgroundColor: colors.card,
                        borderColor: colors.border,
                        opacity: pressed ? 0.7 : 1
                    }
                ]}
            >
                <Ionicons name="language" size={18} color={colors.textSecondary as any} />
                <Text style={[styles.text, { color: colors.textSecondary }]}>
                    {i18n.language.toUpperCase()}
                </Text>
            </Pressable>
        </View>
    );
};

const styles = StyleSheet.create({
    container: {
        position: 'absolute',
        right: 20, // Sağdan boşluk
        top: 10,
        zIndex: 100,
    },
    button: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingVertical: 8,
        paddingHorizontal: 12,
        borderRadius: 20,
        borderWidth: 1,
        elevation: 2,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 1 },
        shadowOpacity: 0.1,
        shadowRadius: 2,
    },
    text: {
        marginLeft: 6,
        fontWeight: '600',
        fontSize: 12,
    },
});
