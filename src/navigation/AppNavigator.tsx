import React from 'react';
import { Platform } from 'react-native';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { NavigationContainer } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import { useTranslation } from 'react-i18next';
import { useTheme } from '../theme';
import { ProfileScreen } from '../screens/ProfileScreen';
import { AboutScreen } from '../screens/AboutScreen';
import { ProjectsScreen } from '../screens/ProjectsScreen';
import { ApplicationsScreen } from '../screens/ApplicationsScreen';
import { AdminScreen } from '../screens/AdminScreen';

export type RootStackParamList = {
  MainTabs: undefined;
  Admin: undefined;
};

export type RootTabParamList = {
  Akış: undefined;
  Hakkımda: undefined;
  Projeler: undefined;
  Uygulamalar: undefined;
};

const Tab = createBottomTabNavigator<RootTabParamList>();
const Stack = createNativeStackNavigator<RootStackParamList>();

const MainTabs = () => {
  const { colors } = useTheme();
  const { t } = useTranslation();

  return (
    <Tab.Navigator
      id="MainTab"
      screenOptions={({ route }) => ({
        headerShown: false,
        tabBarIcon: ({ focused, color, size }) => {
          let iconName: keyof typeof Ionicons.glyphMap;

          if (route.name === 'Akış') {
            iconName = focused ? 'flash' : 'flash-outline';
          } else if (route.name === 'Hakkımda') {
            iconName = focused ? 'person' : 'person-outline';
          } else if (route.name === 'Projeler') {
            iconName = focused ? 'code-slash' : 'code-outline';
          } else if (route.name === 'Uygulamalar') {
            iconName = focused ? 'apps' : 'apps-outline';
          } else {
            iconName = 'help-outline';
          }

          return <Ionicons name={iconName} size={size} color={color} />;
        },
        tabBarActiveTintColor: colors.primary,
        tabBarInactiveTintColor: 'gray',
        tabBarStyle: {
          backgroundColor: colors.background,
          borderTopColor: colors.border,
          // iOS icin Safe Area ayari (Home Indicator'den uzaklastirma)
          paddingBottom: Platform.OS === 'ios' ? 5 : 5,
          paddingTop: 5,
          height: Platform.OS === 'ios' ? 75 : 60,
        },
        tabBarLabelStyle: {
          fontSize: 11,
          fontWeight: '600',
        },
      })}
    >
      <Tab.Screen
        name="Akış"
        component={ProfileScreen}
        options={{ title: t('common.feed') }}
      />
      <Tab.Screen
        name="Hakkımda"
        component={AboutScreen}
        options={{ title: t('common.about') }}
      />
      <Tab.Screen
        name="Projeler"
        component={ProjectsScreen}
        options={{ title: t('common.projects') }}
      />
      <Tab.Screen
        name="Uygulamalar"
        component={ApplicationsScreen}
        options={{ title: t('common.applications') }}
      />
    </Tab.Navigator>
  );
};

export const AppNavigator = () => {
  const { colors } = useTheme();
  const { t } = useTranslation();

  return (
    <NavigationContainer>
      <Stack.Navigator
        id={undefined}
        screenOptions={{
          headerStyle: { backgroundColor: colors.background },
          headerTintColor: colors.text,
          headerTitleStyle: { fontWeight: 'bold' },
        }}
      >
        <Stack.Screen
          name="MainTabs"
          component={MainTabs}
          options={{ headerShown: false, title: '' }}
        />
        <Stack.Screen
          name="Admin"
          component={AdminScreen}
          options={{
            title: t('common.admin'),
            // @ts-ignore
            headerBackTitleVisible: false,
            headerBackTitle: ''
          }}
        />
      </Stack.Navigator>
    </NavigationContainer>
  );
};
