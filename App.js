import { StatusBar } from 'expo-status-bar';
import { StyleSheet, Text, View, Image, Pressable, Linking, Platform } from 'react-native';
import { SafeAreaView, SafeAreaProvider } from 'react-native-safe-area-context';
import { FontAwesome } from '@expo/vector-icons';

const socialLinks = [
  { label: 'GitHub', url: 'https://github.com/smtbcn', icon: 'github', color: '#24292E' },
  { label: 'LinkedIn', url: 'https://www.linkedin.com/in/smtbcn/', icon: 'linkedin', color: '#0A66C2' },
  { label: 'X (Twitter)', url: 'https://x.com/smtbcn', icon: 'twitter', color: '#1DA1F2' },
  { label: 'Instagram', url: 'https://www.instagram.com/smtbcn/', icon: 'instagram', color: '#E1306C' },
  { label: 'Facebook', url: 'https://www.facebook.com/smtbcn', icon: 'facebook', color: '#1877F2' },
];

const SocialButton = ({ label, url, icon, color }) => (
  <Pressable
    onPress={() => Linking.openURL(url)}
    style={({ pressed }) => [styles.button, { backgroundColor: color }, pressed && styles.buttonPressed]}
  >
    <FontAwesome name={icon} size={20} color="#fff" />
    <Text style={styles.buttonText}>{label}</Text>
  </Pressable>
);

export default function App() {
  return (
    <SafeAreaProvider>
      <SafeAreaView style={styles.container} edges={['top','left','right']}>
      <StatusBar style="auto" />
      <View style={styles.header}>
        <Image
          source={{ uri: 'https://avatars.githubusercontent.com/u/75270742?v=4' }}
          style={styles.avatar}
          resizeMode="cover"
        />
        <Text style={styles.name}>Samet BİÇEN</Text>
      </View>

      <View style={styles.buttons}>
        {socialLinks.map((item) => (
          <SocialButton key={item.label} label={item.label} url={item.url} icon={item.icon} color={item.color} />
        ))}
      </View>
    </SafeAreaView>
    </SafeAreaProvider>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#fff',
    paddingHorizontal: 16,
  },

  header: {
    alignItems: 'center',
    paddingTop: Platform.OS === 'ios' ? 24 : 8,
    paddingBottom: 16,
  },
  avatar: {
    width: 120,
    height: 120,
    borderRadius: 60,
    borderWidth: 2,
    borderColor: '#eee',
  },
  name: {
    marginTop: 12,
    fontSize: 18,
    fontWeight: '600',
    color: '#222',
  },
  buttons: {
    marginTop: 8,
  },
  button: {
    paddingVertical: 14,
    paddingHorizontal: 16,
    borderRadius: 10,
    alignItems: 'center',
    alignSelf: 'stretch',
    flexDirection: 'row',
    justifyContent: 'flex-start',
    marginVertical: 6,
  },
  buttonPressed: {
    opacity: 0.85,
  },
  buttonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
    marginLeft: 12,
    textAlign: 'left',
  },
});
