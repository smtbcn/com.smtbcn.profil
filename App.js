import { useState } from 'react';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { ThemeProvider } from './src/theme';
import { ProfileScreen } from './src/screens/ProfileScreen';
import { ProjectsScreen } from './src/screens/ProjectsScreen';
import { SplashScreen } from './src/screens/SplashScreen';

export default function App() {
  const [isReady, setIsReady] = useState(false);
  const [activeTab, setActiveTab] = useState('about');
  const [scrollToAbout, setScrollToAbout] = useState(false);

  const handleTabChange = (tabId) => {
    setActiveTab(tabId);
    setScrollToAbout(false);
  };

  const handleScrollToAbout = () => {
    setActiveTab('about');
    setScrollToAbout(true);
    setTimeout(() => setScrollToAbout(false), 500);
  };

  if (!isReady) {
    return <SplashScreen onFinish={() => setIsReady(true)} />;
  }

  return (
    <SafeAreaProvider>
      <ThemeProvider>
        {activeTab === 'about' ? (
          <ProfileScreen 
            scrollToAbout={scrollToAbout}
            activeTab={activeTab}
            onTabChange={handleTabChange}
            onScrollToAbout={handleScrollToAbout}
          />
        ) : (
          <ProjectsScreen 
            activeTab={activeTab}
            onTabChange={handleTabChange}
            onScrollToAbout={handleScrollToAbout}
          />
        )}
      </ThemeProvider>
    </SafeAreaProvider>
  );
}
