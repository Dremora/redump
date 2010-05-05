class Property < ActiveRecord::Base
  belongs_to :property_group
  has_many :property_values
end
