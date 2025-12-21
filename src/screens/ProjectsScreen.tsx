import React, { useState, useEffect } from 'react';
import { View, Text, ScrollView, Linking, StatusBar, RefreshControl } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useTranslation } from 'react-i18next';
import { useTheme } from '../theme';
import { projectsScreenStyles } from '../styles/projectsScreenStyles';
import { ProjectCard } from '../components/ProjectCard';
import { SkeletonItem } from '../components/SkeletonItem';
import { ProfileService } from '../services/apiService';
import { LanguageSelector } from '../components/LanguageSelector';

interface Project {
  id: number;
  name: string;
  description: string | null;
  html_url: string;
  language: string | null;
  stargazers_count: number;
  forks_count: number;
}

export const ProjectsScreen: React.FC = () => {
  const { isDark, colors } = useTheme();
  const { t } = useTranslation();
  const [projects, setProjects] = useState<Project[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    fetchProjects();
  }, []);

  const fetchProjects = async () => {
    const data = await ProfileService.getProjects();
    if (data) {
      setProjects(data);
    }
    setLoading(false);
  };

  const onRefresh = async () => {
    setRefreshing(true);
    await fetchProjects();
    setRefreshing(false);
  };

  const handleRepoPress = (url: string) => {
    Linking.openURL(url);
  };

  return (
    <SafeAreaView style={[projectsScreenStyles.container, { backgroundColor: colors.background }]} edges={['top', 'left', 'right']}>
      <StatusBar
        barStyle={isDark ? 'light-content' : 'dark-content'}
        backgroundColor={colors.background}
      />

      <ScrollView
        showsVerticalScrollIndicator={false}
        contentContainerStyle={projectsScreenStyles.scrollContent}
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
        <View style={projectsScreenStyles.headerContainer}>
          <Text style={[projectsScreenStyles.title, { color: colors.text }]}>
            {t('projects.title')}
          </Text>
        </View>

        {loading ? (
          <View style={projectsScreenStyles.reposContainer}>
            <SkeletonItem height={110} />
            <SkeletonItem height={110} />
            <SkeletonItem height={110} />
          </View>
        ) : (
          <View style={projectsScreenStyles.reposContainer}>
            <Text style={[projectsScreenStyles.subtitle, { color: colors.textSecondary }]}>
              {t('projects.subtitle', { count: projects.length })}
            </Text>

            {projects.map((project, index) => (
              <ProjectCard
                key={project.id}
                repo={project}
                index={index}
                onPress={() => handleRepoPress(project.html_url)}
              />
            ))}
          </View>
        )}
      </ScrollView>
    </SafeAreaView>
  );
};
