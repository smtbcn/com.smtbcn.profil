import { useRef } from 'react';
import { View, Text, Image, Linking, StatusBar, ScrollView } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useTheme } from '../theme';
import { SocialButton } from '../components/SocialButton';
import { AboutInfo } from '../components/AboutInfo';
import { NavigationMenu } from '../components/NavigationMenu';
import { socialLinks, getPath } from '../constants/socialLinks';
import { profileScreenStyles } from '../styles/profileScreenStyles';

export const ProfileScreen = ({ scrollToAbout, activeTab, onTabChange, onScrollToAbout }) => {
  const { isDark, colors } = useTheme();
  const scrollViewRef = useRef(null);
  const aboutRef = useRef(null);

  const handleSocialPress = (url) => {
    Linking.openURL(url);
  };

  const handleScrollToAbout = () => {
    if (aboutRef.current && scrollViewRef.current) {
      aboutRef.current.measureLayout(
        scrollViewRef.current,
        (x, y) => {
          scrollViewRef.current.scrollTo({ y: y - 20, animated: true });
        },
        () => {}
      );
    }
  };

  if (scrollToAbout) {
    setTimeout(() => handleScrollToAbout(), 100);
  }

  return (
    <SafeAreaView 
      style={[
        profileScreenStyles.container,
        { backgroundColor: colors.background }
      ]}
    >
      <StatusBar 
        barStyle={isDark ? 'light-content' : 'dark-content'} 
        backgroundColor={colors.background}
      />
      
      <ScrollView 
        ref={scrollViewRef}
        showsVerticalScrollIndicator={false}
        contentContainerStyle={profileScreenStyles.scrollContent}
      >
        <View style={profileScreenStyles.header}>
          <Image
            source={{ uri: 'https://avatars.githubusercontent.com/u/75270742?v=4' }}
            style={[
              profileScreenStyles.avatar,
              { borderColor: colors.border }
            ]}
          />
          <Text 
            style={[
              profileScreenStyles.name,
              { color: colors.textSecondary }
            ]}
          >
            Samet BİÇEN
          </Text>
        </View>

        <NavigationMenu 
          activeTab={activeTab} 
          onTabChange={onTabChange}
          onScrollToAbout={onScrollToAbout}
        />

        <View style={profileScreenStyles.buttonsContainer}>
          {socialLinks.map((item) => (
            <SocialButton
              key={item.label}
              label={item.label}
              icon={item.icon}
              color={item.color}
              path={getPath(item.url)}
              onPress={() => handleSocialPress(item.url)}
            />
          ))}
        </View>

        <View ref={aboutRef}>
          <AboutInfo />
        </View>
      </ScrollView>
    </SafeAreaView>
  );
};
