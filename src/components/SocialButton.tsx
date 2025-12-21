import React, { useRef, useEffect } from 'react';
import { Pressable, Text, View, Animated } from 'react-native';
import { FontAwesome } from '@expo/vector-icons';
import { socialButtonStyles } from '../styles/socialButtonStyles';

interface SocialButtonProps {
  label: string;
  icon: string;
  color: string;
  path: string;
  onPress: () => void;
  index?: number;
}

export const SocialButton: React.FC<SocialButtonProps> = ({ label, icon, color, path, onPress, index = 0 }) => {
  const fadeAnim = useRef(new Animated.Value(0)).current;
  const slideAnim = useRef(new Animated.Value(10)).current;

  useEffect(() => {
    Animated.parallel([
      Animated.timing(fadeAnim, {
        toValue: 1,
        duration: 400,
        delay: index * 80,
        useNativeDriver: true,
      }),
      Animated.timing(slideAnim, {
        toValue: 0,
        duration: 400,
        delay: index * 80,
        useNativeDriver: true,
      }),
    ]).start();
  }, [index]);

  return (
    <Animated.View
      style={{
        opacity: fadeAnim,
        transform: [{ translateY: slideAnim }]
      }}
    >
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
                  <FontAwesome name={icon as any} size={20} color="#FFFFFF" />
                </View>
                <Text style={socialButtonStyles.label}>{label}</Text>
              </View>
              <Text style={socialButtonStyles.path}>{path}</Text>
            </View>
          )}
        </Pressable>
      </View>
    </Animated.View>
  );
};
