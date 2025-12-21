import { Pressable, Text, View } from 'react-native';
import { FontAwesome } from '@expo/vector-icons';
import { socialButtonStyles } from '../styles/socialButtonStyles';

export const SocialButton = ({ label, icon, color, path, onPress }) => {
  return (
    <View style={socialButtonStyles.container}>
      <Pressable onPress={onPress}>
        {({ pressed }) => (
          <View
            style={[
              socialButtonStyles.button,
              { backgroundColor: color },
              pressed && socialButtonStyles.buttonPressed,
            ]}
          >
            <View style={socialButtonStyles.leftContent}>
              <View style={socialButtonStyles.icon}>
                <FontAwesome name={icon} size={20} color="#FFFFFF" />
              </View>
              <Text style={socialButtonStyles.label}>{label}</Text>
            </View>
            <Text style={socialButtonStyles.path}>{path}</Text>
          </View>
        )}
      </Pressable>
    </View>
  );
};
