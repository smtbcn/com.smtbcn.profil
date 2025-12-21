import { useRef, useEffect, useState, useCallback } from 'react';
import { View, Text, Image, StatusBar, ScrollView, Pressable, Animated, StyleSheet, Linking, RefreshControl } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useTranslation } from 'react-i18next';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { useTheme } from '../theme';
import { Ionicons } from '@expo/vector-icons';
import { ProfileService } from '../services/apiService';
import { useFocusEffect } from '@react-navigation/native';
import { SkeletonItem } from '../components/SkeletonItem';
import { TimelineItem } from '../components/TimelineItem';
import { LanguageSelector } from '../components/LanguageSelector';

const LANGUAGE_KEY = 'user-language';

export const ProfileScreen: React.FC = () => {
  const { isDark, colors } = useTheme();
  const { t, i18n } = useTranslation();
  const scrollViewRef = useRef<ScrollView>(null);
  const pulseAnim = useRef(new Animated.Value(1)).current;

  const [liveData, setLiveData] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [tapCount, setTapCount] = useState(0);

  const handleAvatarPress = () => {
    const newCount = tapCount + 1;
    if (newCount >= 6) {
      setTapCount(0);
      Linking.openURL('https://profil.milasoft.com.tr/backend/admin/login.php');
    } else {
      setTapCount(newCount);
      // Reset count after 2 seconds of inactivity
      setTimeout(() => setTapCount(0), 2000);
    }
  };

  const fetchLiveData = useCallback(async () => {
    const data = await ProfileService.getProfileData();
    if (data) {
      setLiveData(data);
    }
  }, []);

  useEffect(() => {
    fetchLiveData().then(() => setLoading(false));
  }, [fetchLiveData]);

  const onRefresh = useCallback(async () => {
    setRefreshing(true);
    await fetchLiveData();
    setRefreshing(false);
  }, [fetchLiveData]);

  useFocusEffect(
    useCallback(() => {
      fetchLiveData();
    }, [fetchLiveData])
  );

  useEffect(() => {
    const interval = setInterval(() => {
      fetchLiveData();
    }, 15000);
    return () => clearInterval(interval);
  }, [fetchLiveData]);

  useEffect(() => {
    Animated.loop(
      Animated.sequence([
        Animated.timing(pulseAnim, { toValue: 1.2, duration: 1000, useNativeDriver: true }),
        Animated.timing(pulseAnim, { toValue: 1, duration: 1000, useNativeDriver: true }),
      ])
    ).start();
  }, []);

  const toggleLanguage = async () => {
    const nextLang = i18n.language === 'tr' ? 'en' : 'tr';
    await i18n.changeLanguage(nextLang);
    await AsyncStorage.setItem(LANGUAGE_KEY, nextLang);
  };

  const currentStatusText = liveData
    ? (i18n.language === 'tr' ? liveData.current_activity.tr : liveData.current_activity.en)
    : (loading ? t('common.loading') : t('profile.currentlyDeveloping'));

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'online': return '#34C759';
      case 'busy': return '#FF9500';
      case 'coding': return '#007AFF';
      case 'offline': return '#8E8E93';
      default: return '#34C759';
    }
  };

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: colors.background }} edges={['top', 'left', 'right']}>
      <StatusBar barStyle={isDark ? 'light-content' : 'dark-content'} backgroundColor={colors.background} />

      <ScrollView
        ref={scrollViewRef}
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
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

        {/* Header */}
        <View style={styles.header}>
          <Pressable onPress={handleAvatarPress}>
            <Image source={{ uri: 'https://avatars.githubusercontent.com/u/75270742?v=4' }} style={[styles.avatar, { borderColor: colors.border }]} />
          </Pressable>
          <Text style={[styles.name, { color: colors.text }]}>{t('profile.name')}</Text>
          <Text style={[styles.title, { color: colors.textSecondary }]}>{t('profile.title')}</Text>
        </View>

        {/* Live Status Card */}
        <View style={[styles.statusCard, { backgroundColor: colors.card, borderColor: colors.border }]}>
          <Animated.View style={[styles.statusDot, { backgroundColor: getStatusColor(liveData?.status), transform: [{ scale: pulseAnim }] }]} />
          <View style={{ marginLeft: 12, flex: 1 }}>
            <Text style={[styles.statusLabel, { color: colors.textSecondary }]}>{t('profile.status')}</Text>
            <Text style={[styles.statusText, { color: colors.text }]}>{currentStatusText}</Text>
          </View>
        </View>

        {/* Timeline Section */}
        <View style={styles.timelineSection}>
          <Text style={[styles.sectionTitle, { color: colors.text }]}>Zaman Tüneli</Text>
          {loading ? (
            <View style={{ paddingVertical: 10 }}>
              <SkeletonItem height={100} width="100%" borderRadius={16} marginBottom={16} />
              <SkeletonItem height={100} width="100%" borderRadius={16} marginBottom={16} />
              <SkeletonItem height={100} width="100%" borderRadius={16} />
            </View>
          ) : (
            liveData?.timeline?.map((item: any, index: number) => (
              <TimelineItem
                key={item.id}
                item={item}
                isLast={index === liveData.timeline.length - 1}
              />
            ))
          )}
          {!loading && (!liveData?.timeline || liveData.timeline.length === 0) && (
            <Text style={{ color: colors.textSecondary, textAlign: 'center', marginTop: 20 }}>Henüz bir olay eklenmemiş.</Text>
          )}
        </View>
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1 },
  scrollContent: { paddingHorizontal: 20, paddingBottom: 40 },
  langToggle: { position: 'absolute', right: 0, top: 16, zIndex: 10 },
  langBtn: { flexDirection: 'row', alignItems: 'center', padding: 8, borderRadius: 20, borderWidth: 1 },
  langText: { marginLeft: 6, fontWeight: '600' },
  header: { alignItems: 'center', paddingTop: 40, paddingBottom: 20 },
  avatar: { width: 110, height: 110, borderRadius: 55, borderWidth: 3 },
  name: { fontSize: 22, fontWeight: 'bold', marginTop: 12 },
  title: { fontSize: 16, opacity: 0.8 },
  statusCard: { flexDirection: 'row', alignItems: 'center', padding: 16, borderRadius: 16, borderWidth: 1, marginTop: 10 },
  statusDot: { width: 10, height: 10, borderRadius: 5 },
  statusLabel: { fontSize: 11, fontWeight: 'bold', textTransform: 'uppercase' },
  statusText: { fontSize: 14, fontWeight: '600', marginTop: 2 },
  timelineSection: { marginTop: 30 },
  sectionTitle: { fontSize: 20, fontWeight: 'bold', marginBottom: 20 }
});
