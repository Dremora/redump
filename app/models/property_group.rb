class PropertyGroup < ActiveRecord::Base
  has_and_belongs_to_many :media_types
  has_many :properties
end
