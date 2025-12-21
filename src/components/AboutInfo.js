import { useState, useEffect } from 'react';
import { View, Text, ActivityIndicator } from 'react-native';
import { useTheme } from '../theme';
import { aboutInfoStyles } from '../styles/aboutInfoStyles';

export const AboutInfo = () => {
  const { colors } = useTheme();
  const [aboutText, setAboutText] = useState('');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchAboutInfo();
  }, []);

  const fetchAboutInfo = async () => {
    try {
      const response = await fetch('https://raw.githubusercontent.com/smtbcn/smtbcn/main/README.md');
      const markdown = await response.text();
      
      const paragraphs = parseMarkdownToParagraphs(markdown);
      setAboutText(paragraphs);
      setLoading(false);
    } catch (error) {
      console.error('Error fetching about info:', error);
      setAboutText('Unable to load about information.');
      setLoading(false);
    }
  };

  const parseMarkdownToParagraphs = (markdown) => {
    const lines = markdown.split('\n');
    const paragraphs = [];
    
    for (let line of lines) {
      line = line.trim();
      
      if (line.startsWith('#')) continue;
      if (line.length === 0) continue;
      if (line.startsWith('![')) continue;
      if (line.startsWith('[')) continue;
      if (line.startsWith('---')) continue;
      
      line = line.replace(/\*\*(.*?)\*\*/g, '$1');
      line = line.replace(/\*(.*?)\*/g, '$1');
      line = line.replace(/\[(.*?)\]\(.*?\)/g, '$1');
      
      if (line.length > 0) {
        paragraphs.push(line);
      }
    }
    
    return paragraphs.join('\n\n');
  };

  if (loading) {
    return (
      <View style={aboutInfoStyles.container}>
        <ActivityIndicator size="small" color={colors.textSecondary} />
      </View>
    );
  }

  return (
    <View style={aboutInfoStyles.container}>
      <Text style={[aboutInfoStyles.content, { color: colors.textSecondary }]}>
        {aboutText}
      </Text>
    </View>
  );
};
