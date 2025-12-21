import React, { useRef, useEffect } from 'react';
import { View, Text, Pressable, Animated } from 'react-native';
import { FontAwesome } from '@expo/vector-icons';
import { useTheme } from '../theme';
import { projectsScreenStyles } from '../styles/projectsScreenStyles';
import { getAdaptiveColor } from '../utils/colorUtils';

interface Repo {
    id: number;
    name: string;
    description: string | null;
    language: string | null;
    stargazers_count: number;
    forks_count: number;
}

interface ProjectCardProps {
    repo: Repo;
    onPress: () => void;
    index: number;
}

export const ProjectCard: React.FC<ProjectCardProps> = ({ repo, onPress, index }) => {
    const { colors, isDark } = useTheme();
    const fadeAnim = useRef(new Animated.Value(0)).current;
    const slideAnim = useRef(new Animated.Value(20)).current;

    useEffect(() => {
        Animated.parallel([
            Animated.timing(fadeAnim, {
                toValue: 1,
                duration: 500,
                delay: index * 100,
                useNativeDriver: true,
            }),
            Animated.timing(slideAnim, {
                toValue: 0,
                duration: 500,
                delay: index * 100,
                useNativeDriver: true,
            }),
        ]).start();
    }, [index]);

    const getLanguageColor = (language: string) => {
        const langColors: { [key: string]: string } = {
            JavaScript: '#f1e05a',
            TypeScript: '#3178c6',
            Python: '#3572A5',
            Java: '#b07219',
            PHP: '#4F5D95',
            CSS: '#563d7c',
            HTML: '#e34c26',
            Go: '#00ADD8',
            Ruby: '#701516',
            Swift: '#ffac45',
            Kotlin: '#A97BFF',
            Dart: '#0175C2',
        };
        const baseColor = langColors[language] || '#8b949e';
        return getAdaptiveColor(baseColor, isDark);
    };

    return (
        <Animated.View
            style={{
                opacity: fadeAnim,
                transform: [{ translateY: slideAnim }],
            }}
        >
            <Pressable onPress={onPress}>
                {({ pressed }) => (
                    <View
                        style={[
                            projectsScreenStyles.repoCard,
                            {
                                backgroundColor: colors.card,
                                borderColor: colors.border,
                                elevation: 2,
                                shadowColor: '#000',
                                shadowOffset: { width: 0, height: 2 },
                                shadowOpacity: 0.1,
                                shadowRadius: 4,
                            },
                            pressed && projectsScreenStyles.repoCardPressed,
                        ]}
                    >
                        <View style={projectsScreenStyles.repoHeader}>
                            <FontAwesome name="github" size={20} color={colors.text as any} />
                            <Text style={[projectsScreenStyles.repoName, { color: colors.text }]}>
                                {repo.name}
                            </Text>
                        </View>

                        {repo.description && (
                            <Text
                                numberOfLines={2}
                                style={[projectsScreenStyles.repoDescription, { color: colors.textSecondary }]}
                            >
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
                                <FontAwesome name="star" size={12} color={colors.textSecondary as any} />
                                <Text style={[projectsScreenStyles.repoInfoText, { color: colors.textSecondary }]}>
                                    {repo.stargazers_count}
                                </Text>
                            </View>

                            <View style={projectsScreenStyles.repoInfo}>
                                <FontAwesome name="code-fork" size={12} color={colors.textSecondary as any} />
                                <Text style={[projectsScreenStyles.repoInfoText, { color: colors.textSecondary }]}>
                                    {repo.forks_count}
                                </Text>
                            </View>
                        </View>
                    </View>
                )}
            </Pressable>
        </Animated.View>
    );
};
