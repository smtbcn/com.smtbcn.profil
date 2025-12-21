import { useState, useEffect } from 'react';
import { View, Text, ScrollView, Pressable, Linking, ActivityIndicator, StatusBar } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { FontAwesome } from '@expo/vector-icons';
import { useTheme } from '../theme';
import { NavigationMenu } from '../components/NavigationMenu';
import { projectsScreenStyles } from '../styles/projectsScreenStyles';

export const ProjectsScreen = ({ activeTab, onTabChange, onScrollToAbout }) => {
  const { isDark, colors } = useTheme();
  const [repos, setRepos] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchRepos();
  }, []);

  const fetchRepos = async () => {
    try {
      const response = await fetch('https://api.github.com/users/smtbcn/repos?sort=updated&per_page=20');
      const data = await response.json();
      setRepos(data);
      setLoading(false);
    } catch (error) {
      console.error('Error fetching repos:', error);
      setLoading(false);
    }
  };

  const handleRepoPress = (url) => {
    Linking.openURL(url);
  };

  return (
    <SafeAreaView style={[projectsScreenStyles.container, { backgroundColor: colors.background }]}>
      <StatusBar 
        barStyle={isDark ? 'light-content' : 'dark-content'} 
        backgroundColor={colors.background}
      />
      
      <ScrollView 
        showsVerticalScrollIndicator={false}
        contentContainerStyle={projectsScreenStyles.scrollContent}
      >
        <View style={projectsScreenStyles.headerContainer}>
          <Text style={[projectsScreenStyles.title, { color: colors.text }]}>
            GitHub Projelerim
          </Text>
        </View>

        <NavigationMenu 
          activeTab={activeTab} 
          onTabChange={onTabChange}
          onScrollToAbout={onScrollToAbout}
        />

        {loading ? (
          <View style={projectsScreenStyles.loadingContainer}>
            <ActivityIndicator size="large" color={colors.textSecondary} />
            <Text style={[projectsScreenStyles.loadingText, { color: colors.textSecondary }]}>
              Projeler yükleniyor...
            </Text>
          </View>
        ) : (
          <View style={projectsScreenStyles.reposContainer}>
            <Text style={[projectsScreenStyles.subtitle, { color: colors.textSecondary }]}>
              {repos.length} proje
            </Text>

            {repos.map((repo) => (
              <Pressable
                key={repo.id}
                onPress={() => handleRepoPress(repo.html_url)}
              >
                {({ pressed }) => (
                  <View
                    style={[
                      projectsScreenStyles.repoCard,
                      { backgroundColor: colors.background, borderColor: colors.border },
                      pressed && projectsScreenStyles.repoCardPressed,
                    ]}
                  >
                    <View style={projectsScreenStyles.repoHeader}>
                      <FontAwesome name="github" size={20} color={colors.text} />
                      <Text style={[projectsScreenStyles.repoName, { color: colors.text }]}>
                        {repo.name}
                      </Text>
                    </View>

                    {repo.description && (
                      <Text style={[projectsScreenStyles.repoDescription, { color: colors.textSecondary }]}>
                        {repo.description}
                      </Text>
                    )}

                    <View style={projectsScreenStyles.repoFooter}>
                      {repo.language && (
                        <View style={projectsScreenStyles.repoInfo}>
                          <View style={[projectsScreenStyles.languageDot, { backgroundColor: getLanguageColor(repo.language) }]} />
                          <Text style={[projectsScreenStyles.repoInfoText, { color: colors.textSecondary }]}>
                            {repo.language}
                          </Text>
                        </View>
                      )}
                      
                      <View style={projectsScreenStyles.repoInfo}>
                        <FontAwesome name="star" size={12} color={colors.textSecondary} />
                        <Text style={[projectsScreenStyles.repoInfoText, { color: colors.textSecondary }]}>
                          {repo.stargazers_count}
                        </Text>
                      </View>

                      <View style={projectsScreenStyles.repoInfo}>
                        <FontAwesome name="code-fork" size={12} color={colors.textSecondary} />
                        <Text style={[projectsScreenStyles.repoInfoText, { color: colors.textSecondary }]}>
                          {repo.forks_count}
                        </Text>
                      </View>
                    </View>
                  </View>
                )}
              </Pressable>
            ))}
          </View>
        )}
      </ScrollView>
    </SafeAreaView>
  );
};

const getLanguageColor = (language) => {
  const colors = {
    JavaScript: '#f1e05a',
    TypeScript: '#2b7489',
    Python: '#3572A5',
    Java: '#b07219',
    PHP: '#4F5D95',
    CSS: '#563d7c',
    HTML: '#e34c26',
    Go: '#00ADD8',
    Ruby: '#701516',
    Swift: '#ffac45',
  };
  return colors[language] || '#8b949e';
};
