import { View, Text, Pressable, ScrollView } from 'react-native';
import { useTheme } from '../theme';
import { navigationMenuStyles } from '../styles/navigationMenuStyles';

export const NavigationMenu = ({ activeTab, onTabChange, onScrollToAbout }) => {
  const { colors } = useTheme();

  const tabs = [
    { id: 'about', label: 'Hakkımda' },
    { id: 'projects', label: 'Projeler' },
    { id: 'applications', label: 'Uygulamalar' },
  ];

  const handleTabPress = (tabId) => {
    if (tabId === 'about') {
      onScrollToAbout();
    } else {
      onTabChange(tabId);
    }
  };

  return (
    <View style={[navigationMenuStyles.container, { backgroundColor: colors.background }]}>
      <ScrollView
        horizontal
        showsHorizontalScrollIndicator={false}
        contentContainerStyle={navigationMenuStyles.scrollContent}
      >
        {tabs.map((tab) => (
          <Pressable
            key={tab.id}
            onPress={() => handleTabPress(tab.id)}
            style={navigationMenuStyles.tabButton}
          >
            {({ pressed }) => (
              <View
                style={[
                  navigationMenuStyles.tab,
                  activeTab === tab.id && navigationMenuStyles.activeTab,
                  pressed && navigationMenuStyles.pressedTab,
                ]}
              >
                <Text
                  style={[
                    navigationMenuStyles.tabText,
                    { color: colors.textSecondary },
                    activeTab === tab.id && navigationMenuStyles.activeTabText,
                  ]}
                >
                  {tab.label}
                </Text>
              </View>
            )}
          </Pressable>
        ))}
      </ScrollView>
    </View>
  );
};
